<?php
namespace App\Services\Auth;

use App\Mail\ResetPasswordMail;
use App\Mail\SendMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{
    /**
     * check if the email is registered
     *
     * @author growexx
     * @param  $email string
     * @return boolean
     */
    public function isRegistered($email) {
      $user = User::where('email', $email);
      return $user->count() === 0;
    }

    /**
     * check if the user is enabled
     *
     * @author growexx
     * @param  $email string
     * @return boolean
     */
    public function isEnabled($email) {
      $user = User::where('email', $email)->first();
      return $user->isEnabled();
    }
    
    /**
     * attempt to login with credentials and return token if successful, with token valid time based on remember me
     *
     * @author growexx
     * @param  $request object
     * @return string
     */
    public function attemptLogin($request) {
      $validFor = $request['remember_me'] ? PHP_INT_MAX : 180;
      $credentials = $request->only('email', 'password');
      return auth('api')->setTTL($validFor)->attempt($credentials);
    }
    
    /**
     * handle login logic and return appropriate response body and code
     *
     * @author growexx
     * @param  $request object
     * @return array
     */
    public function login($request) {
      if ($this->isRegistered($request['email'])) {
        $responseBody = [
            'message' => 'User not registered.',
        ];
        $responseStatus = Response::HTTP_UNAUTHORIZED;
      } else if(!$this->isEnabled($request['email'])) {
          $responseBody = [
              'message' => 'Clinic is disabled.',
          ];
          $responseStatus = Response::HTTP_UNAUTHORIZED;
      } else {
          $token = $this->attemptLogin($request);
          if (!$token) {
              $responseBody = [
                  'message' => 'Incorrect password.',
              ];
              $responseStatus = Response::HTTP_UNAUTHORIZED;
          } else {
              $user = Auth::user();
              $user->clinic;
              $responseBody = [
                  'message' => 'Logged in successfully.',
                  'user' => $user,
                  'token' => $token
              ];
              $responseStatus = Response::HTTP_OK;
          }
      }
      return [
        'responseBody' => $responseBody,
        'statusCode' => $responseStatus
      ];
    }
    
    /**
     * logout the currently logged in user
     *
     * @author growexx
     * @return void
     */
    public function logout() {
      auth('api')->logout();
    }

    /**
     * generates a new password reset token
     *
     * @author growexx
     * @param $email string
     * @return string
     */
    public function generateToken($email) {
      return DB::transaction(function () use ($email) {
        $token = Str::random(60);
        $expiresAt = Carbon::now()->addDay();
        PasswordResetToken::where(['email' => $email])->delete();
        PasswordResetToken::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
        return $token;
      });
    }

    /**
     * send the reset password link email
     *
     * @author growexx
     * @return void
     */
    public function sendResetLinkMail($email, $token) {
      Mail::to($email)->send(new ResetPasswordMail($token));
    }

    /**
     * handle forgot password login to generate token for a email, send password reset email and generate response
     *
     * @author growexx
     * @param $request Request
     * @return array
     */
    public function forgotPassword($request) {
      $email = $request->input('email');
      $user = User::where('email', $email)->first();
      if ($user) {
        $token = $this->generateToken($user->email);
        $this->sendResetLinkMail($email, $token);
        $responseBody = [
          'message' => 'Password reset link is send to your email.'
        ];
        $responseStatus = Response::HTTP_OK;
      } else {
        $responseBody = [
          'message' => 'Match not found. Enter registered email.'
        ];
        $responseStatus = Response::HTTP_BAD_REQUEST;
      }
      return [
        'responseBody' => $responseBody,
        'statusCode' => $responseStatus
      ];
    }

    /**
     * check if the password reset token is valid
     *
     * @author growexx
     * @param $passwordResetToken PasswordResetToken::class
     * @return boolean
     */
    public function isTokenValid($passwordResetToken) {
      return DB::transaction(function () use ($passwordResetToken) {
        if(!$passwordResetToken) {
          return false;
        } else if($passwordResetToken->expires_at >= Carbon::now()) {
          return true;
        } else {
          PasswordResetToken::where('token',$passwordResetToken->token)->delete();
          return false;
        }
      });
    }

    /**
     * handle check password reset token api logic
     *
     * @author growexx
     * @param $request Request
     * @return array
     */
    public function checkChangePasswordToken($request) {
      $token = $request->input('token');
      $passwordResetToken = PasswordResetToken::where('token', $token)->first();
      if($this->isTokenValid($passwordResetToken)) {
        $responseBody = [
          'message' => 'Link valid.'
        ];
        $responseStatus = Response::HTTP_OK;
      } else {
        $responseBody = [
          'message' => 'Link expired.'
        ];
        $responseStatus = Response::HTTP_UNAUTHORIZED;
      }
      return [
        'responseBody' => $responseBody,
        'statusCode' => $responseStatus
      ];
    }

    /**
     * update password and delete token
     *
     * @author growexx
     * @param $passwordResetToken PasswordResetToken::class, $password string
     * @return void
     */
    public function updatePassword($passwordResetToken, $password) {
      return DB::transaction(function () use ($passwordResetToken, $password) {
        User::where('email', $passwordResetToken->email)->update(['password' => Hash::make($password)]);
        PasswordResetToken::where('token',$passwordResetToken->token)->delete();
      });
    }

    /**
     * handle change password api logic
     *
     * @author growexx
     * @param $request Request
     * @return array
     */
    public function changePassword($request) {
      $passwordResetToken = PasswordResetToken::where('token',$request->input('token'))->first();
      if($this->isTokenValid($passwordResetToken)) {
        $this->updatePassword($passwordResetToken, $request->input('password'));
        $responseBody = [
          'message' => 'Password updated successfully.'
        ];
        $responseStatus = Response::HTTP_OK;
      } else {
        $responseBody = [
          'message' => 'Link expired.'
        ];
        $responseStatus = Response::HTTP_UNAUTHORIZED;
      }
      return [
        'responseBody' => $responseBody,
        'statusCode' => $responseStatus
      ];
    }
}