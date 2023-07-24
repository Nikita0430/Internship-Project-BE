<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\Notification\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    private $internalServerError = 'Something went wrong.';
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

/**
 * @OA\Get(
 *   path="/api/notifications",
 *   summary="Get user notifications",
 *   description="Returns notifications for the authenticated user",
 *   operationId="getNotifications",
 *   security={ {"bearerAuth": {} }},
 *   tags={"notification"},
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Success"),
 *       @OA\Property(property="unread_count", type="integer", example=1),
 *       @OA\Property(
 *         property="notifications",
 *         type="array",
 *         @OA\Items(
 *           @OA\Property(property="id", type="integer", example=1),
 *           @OA\Property(property="order_id", type="integer", example=1),
 *           @OA\Property(property="status_change", type="string", example="confirmed"),
 *           @OA\Property(property="is_seen", type="boolean", example=false),
 *           @OA\Property(property="created_at", type="string", format="date-time")
 *         ),
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=401,
 *     description="When the token is expired, Unauthenticated message will be sent, If admin tries to call the api unauthorized will be sent",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthenticated."),
 *     )
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="No Notifications Found",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="No Notifications Found")
 *     )
 *   ),
 * )
 */
    public function index(Request $request){
        try {
            $response = $this->notificationService->index();
            return response()->json($response['responseBody'],$response['responseCode']);
        } catch (Exception $e){
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

/**
 * @OA\Patch(
 *   path="/api/notifications",
 *   summary="Update the seen status of all notifications of a clinic user",
 *   operationId="updateNotificationStatus",
 *   security={ {"bearerAuth": {} }},
 *   tags={"notification"},
 *   @OA\Response(
 *     response="200",
 *     description="Notification status updated",
 *     @OA\JsonContent(
 *       @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Notification Status Updated"
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=401,
 *     description="When the token is expired, Unauthenticated message will be sent",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthenticated."),
 *     )
 *   ),
 *   @OA\Response(
 *     response="400",
 *     description="Admin Does Not Have Notifications",
 *     @OA\JsonContent(
 *       @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Admin  Does Not Have Notifications"
 *       )
 *     )
 *   )
 * )
 */
    public function updateSeen(Request $request){
        try {
            $response = $this->notificationService->updateSeen();
            return response()->json($response['responseBody'],$response['responseCode']);
        } catch (Exception $e){
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
