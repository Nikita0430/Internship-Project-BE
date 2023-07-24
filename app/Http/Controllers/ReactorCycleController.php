<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddReactorCycleRequest;
use App\Http\Requests\EditReactorCycleRequest;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use App\Services\ReactorCycle\ReactorCycleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ReactorCycleController extends Controller
{
    private $reactorCycleService;
    private $internalServerError = 'Something went wrong.';

    public function __construct(ReactorCycleService $reactorCycleService)
    {
        $this->reactorCycleService = $reactorCycleService;
    }
    /**
     * @OA\Get(
     *   path="/api/reactor-cycles",
     *   summary="Get list of reactor cycles",
     *   description="Get paginated list of reactor cycles with an optional parameter to set the number of results per page and name to filter the records",
     *   operationId="reactorCycleList",
     *   security={ {"bearerAuth": {} }},
     *   tags={"reactor-cycle"},
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     description="Number of results per page",
     *     required=false,
     *     @OA\Schema(
     *       type="integer",
     *       default=10
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page Number",
     *     required=false,
     *     @OA\Schema(
     *       type="integer",
     *       default=1
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Filter by Reactor Cycle Name",
     *     required=false,
     *     @OA\Schema(
     *       type="string",
     *       default="Cycle"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="sort_by",
     *     in="query",
     *     description="'id', 'cycle_name', 'reactor_name', 'mass', 'target_start_date', 'expiration_date' can be passed to sort the data",
     *     required=false,
     *     @OA\Schema(
     *       type="string",
     *       enum={"", "id", "cycle_name", "reactor_name", "mass", "target_start_date", "expiration_date"}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="sort_order",
     *     in="query",
     *     description="'asc' or 'desc' for sorting in ascending or descending order. not passing is equivalent to 'asc'",
     *     required=false,
     *     @OA\Schema(
     *       type="string",
     *       enum={"", "asc", "desc"}
     *     )
     *   ),
     *   @OA\Response(
     *     description="Success",
     *     response="200",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="reactor-cycles",
     *         type="object",
     *         @OA\Property(property="current_page", type="integer", example=2),
     *         @OA\Property(
     *           property="data",
     *           type="array",
     *           @OA\Items(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="cycle_name", type="string", example="ExampleCycle"),
     *             @OA\Property(property="reactor_name", type="string", example="ExampleReactor"),
     *             @OA\Property(property="mass", type="number", example=120.50),
     *             @OA\Property(property="target_start_date", type="string", format="date"),
     *             @OA\Property(property="expiration_date", type="string", format="date"),
     *             @OA\Property(property="is_enabled", type="boolean")
     *           ),
     *         ),
     *         @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/reactor-cycle?page=1"),
     *         @OA\Property(property="from", type="integer", example=4),
     *         @OA\Property(property="last_page", type="integer", example=3 ),
     *         @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/reactor-cycle?page=3"),
     *         @OA\Property(
     *           property="links",
     *           type="array",
     *           @OA\Items(
     *             type="object",
     *             @OA\Property(property="url", type="string", example="http://localhost:8000/api/reactor-cycle?page=1"),
     *             @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *             @OA\Property(property="active", type="boolean", example=false),
     *           )
     *         ),
     *         @OA\Property(property="next_page_url", type="string", example="http://localhost:8000/api/reactor-cycle?page=3"),
     *         @OA\Property(property="path", type="integer", example="http://localhost:8000/api/reactor-cycle"),
     *         @OA\Property(property="per_page", type="integer", example=3 ),
     *         @OA\Property(property="prev_page_url", type="string", example="http://localhost:8000/api/reactor-cycle?page=1"),
     *         @OA\Property(property="to", type="string", example=6),
     *         @OA\Property(property="total", type="string", example=9),
     *       ),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="No Records Found",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="No Records Found"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="When the token is expired, Unauthenticated message will be sent and when the user is not admin unauthorized message will be sent",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized"),
     *     )
     *   )
     * )
     */

     public function index(Request $request)
    {
        try {
            $response = $this->reactorCycleService->index($request);
            return response()->json($response['responseBody'], $response['statusCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * @OA\Get(
     *   path="/api/reactor-cycles/archived",
     *   summary="Get list of archived reactor cycles",
     *   description="Get paginated list of archived reactor cycles with an optional parameter to set the number of results per page and name to filter the records",
     *   operationId="archivedReactorCycleList",
     *   security={ {"bearerAuth": {} }},
     *   tags={"reactor-cycle"},
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     description="Number of results per page",
     *     required=false,
     *     @OA\Schema(
     *       type="integer",
     *       default=10
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page Number",
     *     required=false,
     *     @OA\Schema(
     *       type="integer",
     *       default=1
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Filter by Reactor Cycle Name",
     *     required=false,
     *     @OA\Schema(
     *       type="string",
     *       default="Cycle"
     *     )
     *   ),
     *   @OA\Response(
     *     description="Success",
     *     response="200",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="reactor-cycles",
     *         type="object",
     *         @OA\Property(property="current_page", type="integer", example=2),
     *         @OA\Property(
     *           property="data",
     *           type="array",
     *           @OA\Items(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="cycle_name", type="string", example="ExampleCycle"),
     *             @OA\Property(property="reactor_name", type="string", example="ExampleReactor"),
     *             @OA\Property(property="mass", type="number", example=120.50),
     *             @OA\Property(property="target_start_date", type="string", format="date"),
     *             @OA\Property(property="expiration_date", type="string", format="date"),
     *             @OA\Property(property="is_enabled", type="boolean"),
     *             @OA\Property(property="archived_status", type="string", example="Expired")
     *           ),
     *         ),
     *         @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/reactor-cycle/archived?page=1"),
     *         @OA\Property(property="from", type="integer", example=4),
     *         @OA\Property(property="last_page", type="integer", example=3 ),
     *         @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/reactor-cycle/archived?page=3"),
     *         @OA\Property(
     *           property="links",
     *           type="array",
     *           @OA\Items(
     *             type="object",
     *             @OA\Property(property="url", type="string", example="http://localhost:8000/api/reactor-cycle/archived?page=1"),
     *             @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *             @OA\Property(property="active", type="boolean", example=false),
     *           )
     *         ),
     *         @OA\Property(property="next_page_url", type="string", example="http://localhost:8000/api/reactor-cycle/archived?page=3"),
     *         @OA\Property(property="path", type="integer", example="http://localhost:8000/api/reactor-cycle/archived"),
     *         @OA\Property(property="per_page", type="integer", example=3 ),
     *         @OA\Property(property="prev_page_url", type="string", example="http://localhost:8000/api/reactor-cycle/archived?page=1"),
     *         @OA\Property(property="to", type="string", example=6),
     *         @OA\Property(property="total", type="string", example=9),
     *       ),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="No Records Found",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="No Records Found"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="When the token is expired, Unauthenticated message will be sent and when the user is not admin unauthorized message will be sent",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized"),
     *     )
     *   )
     * )
     */
    public function getArchived(Request $request)
    {
        try {
            $response = $this->reactorCycleService->getArchived($request);
            return response()->json($response['responseBody'], $response['responseCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/reactor-cycles/avail",
     *   summary="Get list of available reactor cycles",
     *   description="Get list of available reactor cycles",
     *   operationId="availReactorCycleList",
     *   security={ {"bearerAuth": {} }},
     *   tags={"reactor-cycle"},
     *   @OA\Parameter(
     *     name="reactor_name",
     *     in="query",
     *     description="Name of Reactor",
     *     required=true,
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="injection_date",
     *     in="query",
     *     description="Injection Date in YYYY-MM-DD",
     *     required=true,
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="order_id",
     *     in="query",
     *     description="to get list of reactor cycles in view order",
     *     required=false,
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Response(
     *     description="Success",
     *     response="200",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="reactor-cycles",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="name", type="string", example="ExampleCycle"),
     *           @OA\Property(property="reactor_id", type="string", example=1),
     *           @OA\Property(property="mass", type="number", example=120.50),
     *           @OA\Property(property="target_start_date", type="string", format="date"),
     *           @OA\Property(property="expiration_date", type="string", format="date"),
     *           @OA\Property(property="is_enabled", type="boolean")
     *         ),
     *       ),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Missing required query parameter(s) or Reactor does not exist",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Missing required query parameter(s)"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="No Reactor Cycle Exists",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="No Reactor Cycle Exists"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="When the token is expired, Unauthenticated message will be sent",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated."),
     *     )
     *   )
     * )
     */
    public function getAvailable(Request $request)
    {
        try {
            $reactorName = $request->input('reactor_name');
            $injectionDate = $request->input('injection_date');
            $orderID = $request->input('order_id', null);

            return $this->reactorCycleService->getAvailable($reactorName, $injectionDate, $orderID);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/reactor-cycles",
     *   summary="Add Reactor Cycle",
     *   description="Admin can add a reactor cycle",
     *   operationId="reactorCycleAdd",
     *   security={ {"bearerAuth": {} }},
     *   tags={"reactor-cycle"},
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       required={"cycle_name","reactor_name","mass","target_start_date","expiration_date"},
     *       @OA\Property(property="cycle_name", type="string", example="ExampleCycle"),
     *       @OA\Property(property="reactor_name", type="string", example="ExampleReactor"),
     *       @OA\Property(property="mass", type="number", example=120.50),
     *       @OA\Property(property="target_start_date", type="string", format="date"),
     *       @OA\Property(property="expiration_date", type="string", format="date")
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Reactor Cycle Added Successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Reactor Cycle Added"),
     *       @OA\Property(
     *         property="reactor_cycle",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="cycle_name", type="string", example="ExampleCycle"),
     *         @OA\Property(property="reactor_name", type="string", example="ExampleReactor"),
     *         @OA\Property(property="mass", type="number", example=120.50),
     *         @OA\Property(property="target_start_date", type="string", format="date"),
     *         @OA\Property(property="expiration_date", type="string", format="date"),
     *         @OA\Property(property="is_enabled", type="boolean")
     *       ),
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="When the token is expired, Unauthenticated message will be sent and when the user is not admin unauthorized message will be sent",
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
     *           property="cycle_name",
     *           type="array",
     *           @OA\Items(example="The cycle_name field is required."),
     *         )
     *       ),
     *     )
     *   )
     * )
     */
    public function store(AddReactorCycleRequest $request)
    {
        try {
            $result = $this->reactorCycleService->store($request);
            return response()->json($result['responseBody'], $result['statusCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/reactor-cycles/{id}",
     *   summary="Show Reactor Cycle",
     *   description="Admin can get a reactor cycle's details",
     *   operationId="reactorCycleShow",
     *   security={ {"bearerAuth": {} }},
     *   tags={"reactor-cycle"},
     *   @OA\Parameter(
     *      name="id",
     *      description="Reactor Cycle ID",
     *      example=1,
     *      required=true,
     *      in="path",
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Reactor Cycle Found",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Reactor Cycle Found"),
     *       @OA\Property(
     *         property="reactor-cycle",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="cycle_name", type="string", example="ExampleCycle"),
     *         @OA\Property(property="reactor_name", type="string", example="ExampleReactor"),
     *         @OA\Property(property="mass", type="number", example=120.50),
     *         @OA\Property(property="target_start_date", type="string", format="date"),
     *         @OA\Property(property="expiration_date", type="string", format="date"),
     *         @OA\Property(property="is_enabled", type="boolean")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="When the token is expired, Unauthenticated message will be sent and when the user is not admin unauthorized message will be sent",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Reactor Cycle ID does not exist",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Reactor Not Found"),
     *     )
     *   )
     * )
     */
    public function show(Request $request, $reactorCycleId)
    {

        try{
            $response = $this->reactorCycleService->show($reactorCycleId);
            return response()->json($response['responseBody'], $response['statusCode']);
        }
        catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    /**
     * @OA\Put(
     *   path="/api/reactor-cycles/{id}",
     *   summary="Update Reactor Cycle",
     *   description="Admin can update a reactor cycle",
     *   operationId="reactorCycleUpdate",
     *   security={ {"bearerAuth": {} }},
     *   tags={"reactor-cycle"},
     *   @OA\Parameter(
     *      name="id",
     *      description="Reactor Cycle ID",
     *      example=1,
     *      required=true,
     *      in="path",
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       required={"cycle_name","reactor_name","mass","target_start_date","expiration_date"},
     *       @OA\Property(property="cycle_name", type="string", example="ExampleCycle"),
     *       @OA\Property(property="reactor_name", type="string", example="ExampleReactor"),
     *       @OA\Property(property="mass", type="number", example=120.50),
     *       @OA\Property(property="target_start_date", type="string", format="date"),
     *       @OA\Property(property="expiration_date", type="string", format="date")
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Reactor Cycle Updated Successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Reactor Cycle Updated"),
     *       @OA\Property(
     *         property="reactor_cycle",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="cycle_name", type="string", example="ExampleCycle"),
     *         @OA\Property(property="reactor_name", type="string", example="ExampleReactor"),
     *         @OA\Property(property="mass", type="number", example=120.50),
     *         @OA\Property(property="target_start_date", type="string", format="date"),
     *         @OA\Property(property="expiration_date", type="string", format="date"),
     *         @OA\Property(property="is_enabled", type="boolean")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="When the token is expired, Unauthenticated message will be sent and when the user is not admin unauthorized message will be sent",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Reactor Cycle ID does not exist",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Reactor Cycle Not Found"),
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
     *           property="cycle_name",
     *           type="array",
     *           @OA\Items(example="The cycle_name field is required."),
     *         )
     *       ),
     *     )
     *   )
     * )
     */
    public function update(EditReactorCycleRequest $request, $reactorCycleId)
    {
        try {
            $response = $this->reactorCycleService->update($request, $reactorCycleId);
            return response()->json($response['responseBody'], $response['statusCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/reactor-cycles/{id}",
     *   summary="Delete Reactor Cycle",
     *   description="Admin can delete a reactor cycle",
     *   operationId="reactorCycleDelete",
     *   security={ {"bearerAuth": {} }},
     *   tags={"reactor-cycle"},
     *   @OA\Parameter(
     *      name="id",
     *      description="Reactor Cycle ID",
     *      example=1,
     *      required=true,
     *      in="path",
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Reactor Cycle Deleted Successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Reactor Cycle Deleted")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="When the token is expired, Unauthenticated message will be sent and when the user is not admin unauthorized message will be sent",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Reactor Cycle ID does not exist",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Reactor Cycle Not Found"),
     *     )
     *   )
     * )
     */
    public function destroy(Request $request, $reactorCycleId)
    {
        try {
            $response = $this->reactorCycleService->destroy($reactorCycleId);
            return response()->json($response['responseBody'], $response['statusCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Patch(
     *   path="/api/reactor-cycles/{id}/status",
     *   summary="Enable/Disable Reactor Cycle",
     *   description="Admin can enable/diable a reactor cycle",
     *   operationId="reactorCycleStatusUpdate",
     *   security={ {"bearerAuth": {} }},
     *   tags={"reactor-cycle"},
     *   @OA\Parameter(
     *      name="id",
     *      description="Reactor Cycle ID",
     *      example=1,
     *      required=true,
     *      in="path",
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       required={"is_enabled"},
     *       @OA\Property(property="is_enabled", type="boolean", example=true)
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Reactor Cycle Status Updated Successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Reactor Cycle Status Updated")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="When the token is expired, Unauthenticated message will be sent and when the user is not admin unauthorized message will be sent",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Reactor Cycle ID does not exist",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Reactor Cycle Not Found"),
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
     *           property="is_enabled",
     *           type="array",
     *           @OA\Items(example="The is_enabled field is required."),
     *         )
     *       ),
     *     )
     *   )
     * )
     */

     public function updateStatus(Request $request, $reactorCycleId)
    {
        try {
            $response = $this->reactorCycleService->updateStatus($request, $reactorCycleId);
            return response()->json($response['data'], $response['status']);
            
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}