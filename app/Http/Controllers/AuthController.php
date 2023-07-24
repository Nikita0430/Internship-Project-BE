<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\CheckChangePasswordTokenRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Services\Auth\AuthService;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    private $internalServerError = 'Something went wrong.';
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *   path="/api/login",
     *   summary="Login",
     *   description="Login by email, password",
     *   operationId="authLogin",
     *   tags={"auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="password", type="string", format="password", example="adminpass"),
     *       @OA\Property(property="remember_me", type="boolean"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Login Successfully",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="user",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="email", type="string", format="email"),
     *         @OA\Property(property="email_verified_at", type="string", format="date-time"),
     *         @OA\Property(property="role", type="string", example="admin"),
     *         @OA\Property(property="created_at", type="string", format="date-time"),
     *         @OA\Property(property="updated_at", type="string", format="date-time"),
     *       ),
     *       @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="User not registered / Clinic is Disabled / Incorrect Password",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="User not registered"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Request body validation failed",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Email is invalid"),
     *       @OA\Property(
     *         property="errors",
     *         type="object",
     *         @OA\Property(
     *           property="email",
     *           type="array",
     *           @OA\Items(example="The email field is required."),
     *         )
     *       ),
     *     )
     *   )
     * )
     */
    public function login(LoginRequest $request)
    {
        try {
            $response = $this->authService->login($request);
            return response()->json($response['responseBody'],$response['statusCode']);
        } catch (Exception $e){
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/forgot-password",
     *   summary="Forgot Password",
     *   description="Get Change Password Link by entering email address",
     *   operationId="authForgotPassword",
     *   tags={"auth"},
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email")
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Link sent successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Change Password Link Sent")
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Email not Found",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Invalid Email"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Empty/Invalid Email",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Validation Failed"),
     *       @OA\Property(
     *         property="errors",
     *         type="object",
     *         @OA\Property(
     *           property="email",
     *           type="array",
     *           @OA\Items(example="The email field is required."),
     *         )
     *       ),
     *     )
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Internal Server Error",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Something went wrong."),
     *     )
     *   )
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request) {
        try {
            $response = $this->authService->forgotPassword($request);
            return response()->json($response['responseBody'],$response['statusCode']);
        } catch (Exception $e){
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/check-change-password",
     *   summary="Check Validity of Change Password Token",
     *   description="Check Validity of Change Password Token",
     *   operationId="authCheckChangePasswordToken",
     *   tags={"auth"},
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       required={"token"},
     *       @OA\Property(property="token", type="string", example="O1sVEkdDvtWLEmaAzLmEh6tTXdAcvZz6L7uXrA1HzIUDuUTMM1xCXRCtLR0o"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Token is valid",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Link valid.")
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Invalid Token",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Link expired."),
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Empty token",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Validation Failed"),
     *       @OA\Property(
     *         property="errors",
     *         type="object",
     *         @OA\Property(
     *           property="token",
     *           type="array",
     *           @OA\Items(example="The token field is required."),
     *         )
     *       ),
     *     )
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Internal Server Error",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Something went wrong."),
     *     )
     *   )
     * )
     */
    public function checkChangePasswordToken(CheckChangePasswordTokenRequest $request) {
        try {
            $response = $this->authService->checkChangePasswordToken($request);
            return response()->json($response['responseBody'],$response['statusCode']);
        } catch (Exception $e){
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/change-password",
     *   summary="Change Password",
     *   description="Change password by passing token and new password",
     *   operationId="authChangePassword",
     *   tags={"auth"},
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       required={"token","password"},
     *       @OA\Property(property="token", type="string", example="O1sVEkdDvtWLEmaAzLmEh6tTXdAcvZz6L7uXrA1HzIUDuUTMM1xCXRCtLR0o"),
     *       @OA\Property(property="password", type="string", format="password", example="newexamplepass")
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Password Reset Successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Password updated successfully.")
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Invalid Token",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Link expired."),
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Empty token or Password",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Validation Failed"),
     *       @OA\Property(
     *         property="errors",
     *         type="object",
     *         @OA\Property(
     *           property="token",
     *           type="array",
     *           @OA\Items(example="The token field is required."),
     *         )
     *       ),
     *     )
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Internal Server Error",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Something went wrong."),
     *     )
     *   )
     * )
     */
    public function changePassword(ChangePasswordRequest $request){
        try {
            $response = $this->authService->changePassword($request);
            return response()->json($response['responseBody'],$response['statusCode']);
        } catch (Exception $e){
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/logout",
     *   summary="Logout",
     *   description="Logout from the application",
     *   operationId="authLogout",
     *   security={ {"bearerAuth": {} }},
     *   tags={"auth"},
     *   @OA\Response(
     *     response=200,
     *     description="Logged Out Successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Logged out successfully.")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Server Error",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Something went wrong."),
     *     ),
     *   )
     * )
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logout();
            return response()->json([
                'message' => 'Logged out successfully.'
            ], Response::HTTP_OK);
        } catch (Exception $e){
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}