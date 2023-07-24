<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserDetailsRequest;
use App\Services\User\UserDetailService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;

class UserDetailsController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/user-details",
     *     summary="Get user profile",
     *     description="User can get their profile details",
     *     operationId="showUserProfile",
     *     security={{"bearerAuth":{}}},
     *     tags={"User Details"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Profile found"
     *             ),
     *             @OA\Property(
     *                 property="profile",
     *                 type="object",
     *                 @OA\Property(
     *                    property="id",
     *                    type="integer",
     *                    example=1
     *                 ),
     *                 @OA\Property(
     *                    property="email",
     *                    type="string",
     *                    format="email",
     *                    example="clinic1@example.com"
     *                 ),
     *                 @OA\Property(
     *                    property="email_verified_at",
     *                    type="string",
     *                    example=null
     *                 ),
     *                 @OA\Property(
     *                    property="role",
     *                    type="string",
     *                    example="clinic"
     *                 ),
     *                 @OA\Property(
     *                    property="clinic",
     *                    type="object",
     *                    @OA\Property(property="id", type="integer", example=1),
     *                    @OA\Property(property="account_id", type="string", example="C999999"),
     *                    @OA\Property(property="is_enabled", type="boolean", example=true),
     *                    @OA\Property(property="name", type="string", example="exampleName"),
     *                    @OA\Property(property="address", type="string", example="Address Line"),
     *                    @OA\Property(property="city", type="string", example="Example City"),
     *                    @OA\Property(property="state", type="string", example="Example State"),
     *                    @OA\Property(property="zipcode", type="string", example="123456"),
     *                    @OA\Property(property="user_id", type="integer", example="1")
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Clinic is Disabled"
     *             )
     *         )
     *     )     
     *   )
     * )
     */
    private $profileService;

    public function __construct(UserDetailService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function show(Request $request)
    {
        try {
            $response = $this->profileService->getUserProfile($request->user());
            return response()->json($response['data'], $response['status']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     *  @OA\Put(
     *     path="/api/user-details",
     *     summary="Update user profile",
     *     description="Update user profile",
     *     operationId="updateUserProfile",
     *     security={{"bearerAuth":{}}},
     *     tags={"User Details"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *            required={"name", "address", "city", "state", "zipcode"},
     *             @OA\Property(property="name", type="string", example="exampleName"),
     *             @OA\Property(property="address", type="string", example="Address Line"),
     *             @OA\Property(property="city", type="string", example="Example City"),
     *             @OA\Property(property="state", type="string", example="Example State"),
     *             @OA\Property(property="zipcode", type="string", example="123456"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *          @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Profile updated successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *     response=401,
     *     description="When the token is expired, Unauthenticated message will be sent and when the user is admin unauthorized message will be sent",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized"),
     *     )
     *   ),
     *     @OA\Response(
     *     response=422,
     *     description="Validation failed on one or more fields",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Validation Failed"),
     *       @OA\Property(
     *         property="errors",
     *         type="object",
     *         @OA\Property(
     *           property="status",
     *           type="array",
     *           @OA\Items(example="The country should be string."),
     *         )
     *       ),
     *     )
     *   )
     * )
     */

    public function update(UpdateUserDetailsRequest $request)
    {
        try {
            $response = $this->profileService->updateUserProfile($request->user(), $request->all());
            return response()->json($response['responseBody'], $response['responseCode']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}