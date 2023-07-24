<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReactorAvailabilityRequest;
use App\Models\Reactor;
use App\Services\Calendar\CalendarService;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends Controller
{
    private $calendarService;
    private $internalServerError = 'Something went wrong.';
    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

/**
 * @OA\Get(
 *   path="/api/reactors",
 *   summary="Get Reactors",
 *   description="Get List of reactors",
 *   operationId="reactorsList",
 *   security={ {"bearerAuth": {} }},
 *   tags={"calendar"},
 *   @OA\Response(
 *     response=200,
 *     description="Reactor list recieved",
 *     @OA\JsonContent(
 *       @OA\Property(
 *         property="reactors",
 *         type="array",
 *         @OA\Items(example="Reactor1"),
 *       ),
 *     )
 *   ),
 *   @OA\Response(
 *     response=401,
 *     description="When the token is expired, Unauthenticated message will be sent",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthorized"),
 *     )
 *   ),
 * )
 */
    public function getReactorList(Request $request)
    {
        try {
            $response = $this->calendarService->getReactorList();
            return response()->json($response['responseBody'], $response['responseCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

/**
 * @OA\Post(
 *   path="/api/calendar",
 *   summary="Get Availibilities for Reactors",
 *   description="user can get availibilities for Reactors on calendar view",
 *   operationId="calendarAvail",
 *   security={ {"bearerAuth": {} }},
 *   tags={"calendar"},
 *   @OA\RequestBody(
 *     @OA\JsonContent(
 *       required={"reactor_name","month","year"},
 *       @OA\Property(property="reactor_name", type="string", example="Reactor1"),
 *       @OA\Property(property="month", type="integer", example=5),
 *       @OA\Property(property="year", type="integer", example=2023),
 *     ),
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Availibilities obtained",
 *     @OA\JsonContent(
 *       @OA\Property(
 *         property="calendar",
 *         type="array",
 *         @OA\Items(
 *           @OA\Property(property="date", type="string", format="date", example="2023-05-01"),
 *           @OA\Property(property="is_available", type="boolean"),
 *         ),
 *       ),
 *       @OA\Property(property="message", type="string", example="Success"),
 *     ),
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Reactor Not Found",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Reactor Not Found"),
 *     )
 *   ),
 *   @OA\Response(
 *     response=401,
 *     description="When the token is expired, Unauthenticated message will be sent",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthorized"),
 *     )
 *   ),
 *   @OA\Response(
 *     response=422,
 *     description="Validation failed on one or more fields",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Validation Failed"),
 *       @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *           property="reactor_name",
 *           type="array",
 *           @OA\Items(example="The reactor_name field is required."),
 *         )
 *       ),
 *     )
 *   )
 * )
 */
    public function getReactorAvail(ReactorAvailabilityRequest $request) {
        try {
            $response = $this->calendarService->getReactorAvail($request);
            return response()->json($response['responseBody'], $response['responseCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
