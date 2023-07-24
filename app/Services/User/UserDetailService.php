<?php
namespace App\Services\User;


use App\Models\Clinic;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserDetailService
{
    /**
     * Retrieves the user profile information.
     *
     * @author Growexx
     * @param User $user
     * @return array
     */
    public function getUserProfile(User $user)
    {
        if ($user->isAdmin() || !$user->clinic->is_enabled) {
            $responseBody = [
                'message' => 'Unauthorized',
            ];
            $responseStatus = Response::HTTP_UNAUTHORIZED;
        } else {
            $responseBody = [
                'message' => 'Profile found',
                'profile' => [
                    'email' => $user->email,
                    'account_id' => $user->clinic->account_id,
                    'name' => $user->clinic->name,
                    'address' => $user->clinic->address,
                    'city' => $user->clinic->city,
                    'state' => $user->clinic->state,
                    'zipcode' => $user->clinic->zipcode,
                ],
            ];
            $responseStatus = Response::HTTP_OK;
        }

        return [
            'data' => $responseBody,
            'status' => $responseStatus,
        ];
    }

    /**
     * Updates the user profile information.
     *
     * @author Growexx
     * @param User $user
     * @param array $data
     * @return array
     */
    public function updateUserProfile(User $user, array $data)
    {
        if($user->clinic->name!==$data['name'] && Clinic::where('name', $data['name'])->exists()) {
            $responseBody = [
                'message' => 'Clinic name has already been taken.'
            ];
            $responseCode = Response::HTTP_BAD_REQUEST;
        } else {
            $this->updateDetails($user, $data);
            $responseBody = [
                'message' => 'Profile updated successfully',
            ];
            $responseCode = Response::HTTP_OK;
        }
        return [
            'responseBody' => $responseBody,
            'responseCode' => $responseCode,
        ];
    }

    /**
     * Updates user detail in database.
     *
     * @author Growexx
     * @param User $user
     * @param array $data
     * @return void
     */
    public function updateDetails($user, $data)
    {
        DB::transaction(function () use ($user, $data) {
            $clinic = $user->clinic;
            $clinic->name = $data['name'];
            $clinic->address = $data['address'];
            $clinic->city = $data['city'];
            $clinic->state = $data['state'];
            $clinic->zipcode = $data['zipcode'];
            $clinic->save();
            if (isset($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();
        });
    }
}