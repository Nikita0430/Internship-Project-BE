<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUpdateOrderRequest;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class OrderController extends Controller
{
    private $orderService;
    private $internalServerError = 'Something went wrong.';


    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
/**
 * @OA\Get(
 *   path="/api/orders",
 *   summary="Get list of orders placed by logged in clinic, Get list of all orders for admin user",
 *   description="Get paginated list of orders placed by logged in clinic, get list of all orders for admin user with an optional parameter to set the number of results per page, the result can be in a sorted order using sort_by and sort_order params.",
 *   operationId="ordersList",
 *   security={ {"bearerAuth": {} }},
 *   tags={"order"},
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
 *     name="search",
 *     in="query",
 *     description="Search By Dog Name, or Dog Breed",
 *     required=false,
 *     @OA\Schema(
 *       type="string",
 *       default="Tom"
 *     )
 *   ),
 *   @OA\Parameter(
 *     name="sort_by",
 *     in="query",
 *     description="'order_id', 'clinic_name', 'placed_at', 'shipped_at', 'dosage_per_elbow' or 'total_dosage', sort by a particular column",
 *     required=false,
 *     @OA\Schema(
 *       type="string",
 *       enum={"", "order_id", "clinic_name", "placed_at", "shipped_at", "dosage_per_elbow", "total_dosage"}
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
 *         property="orders",
 *         type="object",
 *         @OA\Property(property="current_page", type="integer", example=2),
 *         @OA\Property(
 *           property="data",
 *           type="array",
 *           @OA\Items(
 *             type="object",
 *             @OA\Property(property="order_id", type="integer", example=123),
 *             @OA\Property(property="clinic_id", type="integer", example=456),
 *             @OA\Property(property="clinic_name", type="string", example="ClinicName"),
 *             @OA\Property(property="account_id", type="string", example="C000456"),
 *             @OA\Property(property="address", type="string", example="Address Line 1"),
 *             @OA\Property(property="city", type="string", example="City"),
 *             @OA\Property(property="state", type="string", example="State"),
 *             @OA\Property(property="zipcode", type="string", example="12345"),
 *             @OA\Property(property="reactor_id", type="integer", example=123),
 *             @OA\Property(property="reactor_name", type="string", example="ReactorName"),
 *             @OA\Property(property="reactor_cycle_id", type="string", example=1),
 *             @OA\Property(property="email", type="string", format="email"),
 *             @OA\Property(property="placed_at", type="string", format="date-time"),
 *             @OA\Property(property="confirmed_at", type="string", format="date-time"),
 *             @OA\Property(property="shipped_at", type="string", format="date-time"),
 *             @OA\Property(property="out_for_delivery_at", type="string", format="date-time"),
 *             @OA\Property(property="delivered_at", type="string", format="date-time"),
 *             @OA\Property(property="cancelled_at", type="string", format="date-time"),
 *             @OA\Property(property="injection_date", type="string", format="date"),
 *             @OA\Property(property="dog_name", type="string", example="Fido"),
 *             @OA\Property(property="dog_breed", type="string", example="Golden Retriever"),
 *             @OA\Property(property="dog_age", type="integer", example=5),
 *             @OA\Property(property="dog_weight", type="number", format="float", example=70.5),
 *             @OA\Property(property="dog_gender", type="string", enum={"male", "female"}, example="male"),
 *             @OA\Property(property="no_of_elbows", type="integer", example=2),
 *             @OA\Property(property="dosage_per_elbow", type="number", example=1.5),
 *             @OA\Property(property="total_dosage", type="number", example=3),
 *             @OA\Property(property="status", type="string", example="pending"),
 *           ),
 *         ),
 *         @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/orders?page=1"),
 *         @OA\Property(property="from", type="integer", example=4),
 *         @OA\Property(property="last_page", type="integer", example=3 ),
 *         @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/orders?page=3"),
 *         @OA\Property(
 *           property="links",
 *           type="array",
 *           @OA\Items(
 *             type="object",
 *             @OA\Property(property="url", type="string", example="http://localhost:8000/api/orders?page=1"),
 *             @OA\Property(property="label", type="string", example="&laquo; Previous"),
 *             @OA\Property(property="active", type="boolean", example=false),
 *           )
 *         ),
 *         @OA\Property(property="next_page_url", type="string", example="http://localhost:8000/api/orders?page=3"),
 *         @OA\Property(property="path", type="integer", example="http://localhost:8000/api/orders"),
 *         @OA\Property(property="per_page", type="integer", example=3 ),
 *         @OA\Property(property="prev_page_url", type="string", example="http://localhost:8000/api/orders?page=1"),
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
 *     description="When the token is expired unauthenticated message will be sent",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthenticated"),
 *     )
 *   )
 * )
 */
    public function index(Request $request){
        try {
            $response = $this->orderService->index($request);
            return response()->json($response['responseBody'], $response['responseCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

/**
 * @OA\Post(
 *     path="/api/orders",
 *     summary="Place an order",
 *     operationId="placeOrder",
 *     security={ {"bearerAuth": {} }},
 *     tags={"order"},
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             required={ "clinic_id", "email", "injection_date", "dog_name", "dog_breed", "dog_age", "dog_weight", "dog_gender", "reactor_name", "reactor_cycle_id", "no_of_elbows", "dosage_per_elbow"},
 *             @OA\Property(property="clinic_id", type="integer", example=1),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="injection_date", type="string", format="date", example="2023-05-01"),
 *             @OA\Property(property="dog_name", type="string", example="Fido"),
 *             @OA\Property(property="dog_breed", type="string", example="Labrador Retriever"),
 *             @OA\Property(property="dog_age", type="integer", example=3),
 *             @OA\Property(property="dog_weight", type="number", example=25.5),
 *             @OA\Property(property="dog_gender", type="string", enum={"male", "female"}, example="male"),
 *             @OA\Property(property="reactor_name", type="string", example="Reactor A"),
 *             @OA\Property(property="reactor_cycle_id", type="integer", example=1),
 *             @OA\Property(property="no_of_elbows", type="integer", example=2),
 *             @OA\Property(property="dosage_per_elbow", type="number", example=1.5),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order placed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Order Placed")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid or missing input parameters, Clinic Not Found / Reactor Not Found / Reactor Cycle Not Found / Reactor does not match selected reactor cycle / Reactor cycle is unavailable for injection date and total dosage",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Clinic Not Found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="When the token is expired, Unauthenticated message will be sent. When the user is not admin and tries to place order as another clinic Unauthorized message will be sent",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object", example={"clinic_id": {"The clinic id field is required."}})
 *         )
 *     )
 * )
 */
    public function store(PlaceOrderRequest $request){
        try {
            $response = $this->orderService->store($request);
            return response()->json($response['responseBody'], $response['statusCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

/**
 * @OA\Get(
 *   path="/api/orders/{id}",
 *   summary="Show Order",
 *   description="Admin can get details of any order, Clinic can get details of any order placed by it",
 *   operationId="orderShow",
 *   security={ {"bearerAuth": {} }},
 *   tags={"order"},
 *   @OA\Parameter(
 *      name="id",
 *      description="Order ID",
 *      example=1,
 *      required=true,
 *      in="path",
 *      @OA\Schema(
 *          type="integer"
 *      )
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Order Found",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Order Found"),
 *       @OA\Property(
 *         property="order",
 *         type="object",
 *         @OA\Property(property="order_id", type="integer", example=123),
 *         @OA\Property(property="clinic_id", type="integer", example=456),
 *         @OA\Property(property="clinic_name", type="string", example="ClinicName"),
 *         @OA\Property(property="account_id", type="string", example="C000456"),
 *         @OA\Property(property="address", type="string", example="Address Line 1"),
 *         @OA\Property(property="city", type="string", example="City"),
 *         @OA\Property(property="state", type="string", example="State"),
 *         @OA\Property(property="zipcode", type="string", example="12345"),
 *         @OA\Property(property="reactor_id", type="integer", example=123),
 *         @OA\Property(property="reactor_name", type="string", example="ReactorName"),
 *         @OA\Property(property="reactor_cycle_id", type="string", example=1),
 *         @OA\Property(property="email", type="string", format="email"),
 *         @OA\Property(property="placed_at", type="string", format="date-time"),
 *         @OA\Property(property="confirmed_at", type="string", format="date-time"),
 *         @OA\Property(property="shipped_at", type="string", format="date-time"),
 *         @OA\Property(property="out_for_delivery_at", type="string", format="date-time"),
 *         @OA\Property(property="delivered_at", type="string", format="date-time"),
 *         @OA\Property(property="cancelled_at", type="string", format="date-time"),
 *         @OA\Property(property="injection_date", type="string", format="date"),
 *         @OA\Property(property="dog_name", type="string", example="Fido"),
 *         @OA\Property(property="dog_breed", type="string", example="Golden Retriever"),
 *         @OA\Property(property="dog_age", type="integer", example=5),
 *         @OA\Property(property="dog_weight", type="number", format="float", example=70.5),
 *         @OA\Property(property="dog_gender", type="string", enum={"male", "female"}, example="male"),
 *         @OA\Property(property="no_of_elbows", type="integer", example=2),
 *         @OA\Property(property="dosage_per_elbow", type="number", example=1.5),
 *         @OA\Property(property="total_dosage", type="number", example=3),
 *         @OA\Property(property="status", type="string", example="pending"),
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=401,
 *     description="When the token is expired, Unauthenticated message will be sent, If clinic user tries to access orders placed by other clinics Unauthorized message will be sent",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthenticated."),
 *     )
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Order ID does not exist",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Order Not Found"),
 *     )
 *   )
 * )
 */
    public function show(Request $request, $orderId){
        try {
            $response = $this->orderService->show($orderId);
            return response()->json($response['responseBody'], $response['statusCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

/**
* @OA\Patch(
*   path="/api/orders/{id}",
*   summary="Change status of Order",
*   description="Admin can change the status of order",
*   operationId="orderStatusUpdate",
*   security={ {"bearerAuth": {} }},
*   tags={"order"},
*   @OA\Parameter(
*      name="id",
*      description="Order ID",
*      example=1,
*      required=true,
*      in="path",
*      @OA\Schema(
*          type="integer"
*      )
*   ),
*   @OA\RequestBody(
*     @OA\JsonContent(
*       required={"status"},
*       @OA\Property(property="status", type="string", enum={"pending", "confirmed", "shipped", "out for delivery", "delivered", "cancelled"}, example="confirmed")
*     ),
*   ),
*   @OA\Response(
*     response=200,
*     description="Order Status Updated Successfully",
*     @OA\JsonContent(
*       @OA\Property(property="message", type="string", example="Order Status Updated")
*     )
*   ),
*   @OA\Response(
*     response=400,
*     description="Invalid Status Change Request",
*     @OA\JsonContent(
*       @OA\Property(property="message", type="string", example="Cannot Revert the Status"),
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
*     description="Order ID does not exist",
*     @OA\JsonContent(
*       @OA\Property(property="message", type="string", example="Order Not Found"),
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
*           property="status",
*           type="array",
*           @OA\Items(example="The status field is required."),
*         )
*       ),
*     )
*   )
* )
*/
    public function updateStatus(UpdateStatusRequest $request, $orderId){
        try {
            $response = $this->orderService->updateStatus($request, $orderId);
            return response()->json($response['responseBody'], $response['responseCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }  
    }

/**
* @OA\Patch(
*   path="/api/orders/bulk",
*   summary="Change status of Multiple orders at once",
*   description="Admin can change the status of orders",
*   operationId="orderStatusUpdateBulk",
*   security={ {"bearerAuth": {} }},
*   tags={"order"},
*   @OA\RequestBody(
*     @OA\JsonContent(
*       required={"status", "orders"},
*       @OA\Property(
*         property="orders",
*         type="array",
*         @OA\Items(example=1)
*       ),
*       @OA\Property(property="status", type="string", enum={"pending", "confirmed", "shipped", "out for delivery", "delivered", "cancelled"}, example="confirmed")
*     ),
*   ),
*   @OA\Response(
*     response=200,
*     description="Order Status Updated Successfully",
*     @OA\JsonContent(
*       @OA\Property(property="message", type="string", example="Order Status Updated")
*     )
*   ),
*   @OA\Response(
*     response=400,
*     description="One or more orders not found / Status cannot be changed for one or more orders",
*     @OA\JsonContent(
*       @OA\Property(property="message", type="string", example="Status cannot be changed for one or more orders"),
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
*           property="status",
*           type="array",
*           @OA\Items(example="The status field is required."),
*         )
*       ),
*     )
*   )
* )
*/
    public function updateStatusBulk(BulkUpdateOrderRequest $request){        
        try {
            $response = $this->orderService->updateStatusBulk($request);
            return response()->json($response['responseBody'], $response['responseCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

/**
 * @OA\Get(
 *   path="/api/orders/download",
 *   summary="Get the list of orders in csv format",
 *   description="Get list of all orders in csv format with sort_by, sort_order, dog_name and dog_breed as params",
 *   operationId="ordersDownload",
 *   security={ {"bearerAuth": {} }},
 *   tags={"order"},
 *   @OA\Parameter(
 *     name="search",
 *     in="query",
 *     description="Search By Dog Name or Dog Breed",
 *     required=false,
 *     @OA\Schema(
 *       type="string",
 *       default="Tom"
 *     )
 *   ),
 *   @OA\Parameter(
 *     name="sort_by",
 *     in="query",
 *     description="'order_id', 'clinic_name', 'placed_at', 'shipped_at', 'dosage_per_elbow' or 'total_dosage', sort by a particular column",
 *     required=false,
 *     @OA\Schema(
 *       type="string",
 *       enum={"", "order_id", "clinic_name", "placed_at", "shipped_at", "dosage_per_elbow", "total_dosage"}
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
 *     response="200",
 *     description="CSV file",
 *     @OA\MediaType(
 *       mediaType="text/csv",
 *       @OA\Schema(
 *         type="string",
 *         format="binary"
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=400,
 *     description="Invalid sorting params",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Invalid sort by parameter"),
 *     )
 *   ),
 *   @OA\Response(
 *     response=401,
 *     description="For Clinic User Request - Unauthorized and for Invalid Token - Unauthenticated",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthenticated"),
 *     )
 *   )
 * )
 */
    public function downloadList(Request $request){
        try {
            $response = $this->orderService->downloadList($request);
            if ($response['responseCode'] === 200) {
                return response($response['responseBody'], $response['responseCode'], [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="Orders.csv"',
                ]);
            } else {
                return response($response['responseBody'], $response['responseCode']);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}