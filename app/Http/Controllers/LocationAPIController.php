<?php

namespace App\Http\Controllers;

use App\Services\Location\LocationAPIService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocationAPIController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/get-locations",
     *     summary="Get locations using PositionStack API",
     *     description="Retrieve locations based on the query parameter",
     *     operationId="getLocations",
     *     security={{"bearerAuth":{}}},
     *     tags={"locations"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Location query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     */

    private $locationService;

    public function __construct(LocationAPIService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function getLocations(Request $request)
    {
        try {
            $apiAccessKey = env('POSITONSTACK_ACCESS_KEY');
            $query = $request->input('query');

            $locations = $this->locationService->getLocations($apiAccessKey, $query);

            if ($locations !== null) {
                return response()->json($locations);
            } else {
                return response()->json([
                    'error' => 'Failed to fetch geocoding data',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}