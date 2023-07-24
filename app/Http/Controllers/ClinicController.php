<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddClinicRequest;
use App\Http\Requests\EditClinicRequest;
use App\Models\Clinic;
use App\Models\User;
use App\Services\Clinic\ManageClinicService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ClinicController extends Controller
{
    private $manageClinicService;
    private $internalServerError = 'Something went wrong.';


    public function __construct(ManageClinicService $manageClinicService)
    {
        $this->manageClinicService = $manageClinicService;
    }

    /**
     * @OA\Get(
     *   path="/api/clinics",
     *   summary="Get list of clinics",
     *   description="Get paginated list of clinics with an optional parameter to set the number of results per page, can pass sort by and sort order ",
     *   operationId="clinicList",
     *   security={ {"bearerAuth": {} }},
     *   tags={"clinic"},
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
     *     description="Filter by Clinic Name",
     *     required=false,
     *     @OA\Schema(
     *       type="string",
     *       default="Clinic"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="sort_by",
     *     in="query",
     *     description="'name', '', can be passed to sort the data",
     *     required=false,
     *     @OA\Schema(
     *       type="string",
     *       enum={"", "name"}
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
     *     description="clinic list will be sent, if no records found appropriate message will be sent",
     *     response="200",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="clinics",
     *         type="object",
     *         @OA\Property(property="current_page", type="integer", example=2),
     *         @OA\Property(
     *           property="data",
     *           type="array",
     *           @OA\Items(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=4),
     *             @OA\Property(property="account_id", type="string", example="C000004"),
     *             @OA\Property(property="is_enabled", type="boolean", example=true),
     *             @OA\Property(property="name", type="string", example="Pagination Clinic28"),
     *             @OA\Property(property="address", type="string", example="address28"),
     *             @OA\Property(property="city", type="string", example="city28"),
     *             @OA\Property(property="state", type="string", example="state28"),
     *             @OA\Property(property="zipcode", type="string", example="123456"),
     *             @OA\Property(property="user_id", type="integer", example=32),
     *           ),
     *         ),
     *         @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/clinics?page=1"),
     *         @OA\Property(property="from", type="integer", example=4),
     *         @OA\Property(property="last_page", type="integer", example=3 ),
     *         @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/clinics?page=3"),
     *         @OA\Property(
     *           property="links",
     *           type="array",
     *           @OA\Items(
     *             type="object",
     *             @OA\Property(property="url", type="string", example="http://localhost:8000/api/clinics?page=1"),
     *             @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *             @OA\Property(property="active", type="boolean", example=false),
     *           )
     *         ),
     *         @OA\Property(property="next_page_url", type="string", example="http://localhost:8000/api/clinics?page=3"),
     *         @OA\Property(property="path", type="integer", example="http://localhost:8000/api/clinics"),
     *         @OA\Property(property="per_page", type="integer", example=3 ),
     *         @OA\Property(property="prev_page_url", type="string", example="http://localhost:8000/api/clinics?page=1"),
     *         @OA\Property(property="to", type="string", example=6),
     *         @OA\Property(property="total", type="string", example=9),
     *       ),
     *     ),
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
            $response = $this->manageClinicService->index($request);
            return response()->json($response['responseBody'], $response['statusCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/clinics",
     *   summary="Add Clinic",
     *   description="Admin can add a clinic",
     *   operationId="clinicAdd",
     *   security={ {"bearerAuth": {} }},
     *   tags={"clinic"},
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       required={"email","password","name","address","city","state","zipcode"},
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="password", type="string", format="password", example="examplepass"),
     *       @OA\Property(property="name", type="string", example="exampleName"),
     *       @OA\Property(property="address", type="string", example="Address Line"),
     *       @OA\Property(property="city", type="string", example="Example City"),
     *       @OA\Property(property="state", type="string", example="Example State"),
     *       @OA\Property(property="zipcode", type="string", example="123456"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Clinic Added Successfully",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="clinic",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="account_id", type="string", example="C999999"),
     *         @OA\Property(property="is_enabled", type="boolean", example=true),
     *         @OA\Property(property="name", type="string", example="exampleName"),
     *         @OA\Property(property="address", type="string", example="Address Line"),
     *         @OA\Property(property="city", type="string", example="Example City"),
     *         @OA\Property(property="state", type="string", example="Example State"),
     *         @OA\Property(property="zipcode", type="string", example="123456"),
     *         @OA\Property(property="user_id", type="integer", example="1")
     *       ),
     *       @OA\Property(property="message", type="string", example="Clinic Added")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="When the token is expired, Unauthenticated message will be sent, and when the user is not admin, an unauthorized message will be sent",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Email Already Exists",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Email Already Exists"),
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
     *         @OA\AdditionalProperties(
     *           type="array",
     *           @OA\Items(example="The email field is required.")
     *         )
     *       )
     *     )
     *   )
     * )
     */

    public function store(AddClinicRequest $request)
    {
        try {
            $response = $this->manageClinicService->store($request);
            return response()->json($response['responseBody'], $response['statusCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/clinics/{id}",
     *   summary="Show Clinic",
     *   description="Admin can get a clinic's details",
     *   operationId="clinicShow",
     *   security={ {"bearerAuth": {} }},
     *   tags={"clinic"},
     *   @OA\Parameter(
     *      name="id",
     *      description="Clinic ID",
     *      example=1,
     *      required=true,
     *      in="path",
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Clinic Found",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Clinic Found"),
     *       @OA\Property(
     *         property="clinic",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="account_id", type="string", example="C999999"),
     *         @OA\Property(property="is_enabled", type="boolean", example=true),
     *         @OA\Property(property="name", type="string", example="exampleName"),
     *         @OA\Property(property="address", type="string", example="Address Line"),
     *         @OA\Property(property="city", type="string", example="Example City"),
     *         @OA\Property(property="state", type="string", example="Example State"),
     *         @OA\Property(property="zipcode", type="string", example="123456"),
     *         @OA\Property(property="user_id", type="integer", example="1")
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
     *     description="Clinic ID does not exist",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Clinic Not Found"),
     *     )
     *   )
     * )
     */
    public function show(Request $request, $clinicId)
    {
        try {
            $response = $this->manageClinicService->show($clinicId);
            return response()->json($response['responseBody'], $response['statusCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *   path="/api/clinics/{id}",
     *   summary="Update Clinic",
     *   description="Admin can update a clinic",
     *   operationId="clinicUpdate",
     *   security={ {"bearerAuth": {} }},
     *   tags={"clinic"},
     *   @OA\Parameter(
     *      name="id",
     *      description="Clinic ID",
     *      example=1,
     *      required=true,
     *      in="path",
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       required={"name","address","city","state","zipcode"},
     *       @OA\Property(property="name", type="string", example="exampleName"),
     *       @OA\Property(property="address", type="string", example="Address Line"),
     *       @OA\Property(property="city", type="string", example="Example City"),
     *       @OA\Property(property="state", type="string", example="Example State"),
     *       @OA\Property(property="zipcode", type="string", example="123456"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Clinic Updated Successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Clinic Updated"),
     *       @OA\Property(
     *         property="clinic",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="account_id", type="string", example="C999999"),
     *         @OA\Property(property="is_enabled", type="boolean", example=true),
     *         @OA\Property(property="name", type="string", example="exampleName"),
     *         @OA\Property(property="address", type="string", example="Address Line"),
     *         @OA\Property(property="city", type="string", example="Example City"),
     *         @OA\Property(property="state", type="string", example="Example State"),
     *         @OA\Property(property="zipcode", type="string", example="123456"),
     *         @OA\Property(property="user_id", type="integer", example="1")
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
     *     description="Clinic ID does not exist",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Clinic Not Found"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Clinic Name already exists",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Clinic name not available."),
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
     *           property="email",
     *           type="array",
     *           @OA\Items(example="The email field is required."),
     *         )
     *       ),
     *     )
     *   )
     * )
     */
    public function update(EditClinicRequest $request, $clinicId)
    {
        try {
            $response = $this->manageClinicService->update($request, $clinicId);
            return response()->json($response['responseBody'], $response['statusCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/clinics/{id}",
     *   summary="Delete Clinic",
     *   description="Admin can delete a clinic",
     *   operationId="clinicDelete",
     *   security={ {"bearerAuth": {} }},
     *   tags={"clinic"},
     *   @OA\Parameter(
     *      name="id",
     *      description="Clinic ID",
     *      example=1,
     *      required=true,
     *      in="path",
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Clinic Deleted Successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Clinic Deleted")
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
     *     description="Clinic ID does not exist",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Clinic Not Found"),
     *     )
     *   )
     * )
     */
    public function destroy(Request $request, $clinicId)
    {
        try {
            $response = $this->manageClinicService->destroy($clinicId);
            return response()->json($response['responseBody'], $response['statusCode']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Patch(
     *   path="/api/clinics/{id}/status",
     *   summary="Enable/Disable Clinic",
     *   description="Admin can enable/diable a clinic",
     *   operationId="clinicStatusUpdate",
     *   security={ {"bearerAuth": {} }},
     *   tags={"clinic"},
     *   @OA\Parameter(
     *      name="id",
     *      description="Clinic ID",
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
     *     description="Clinic Updated Successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Clinic Status Updated")
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
     *     description="Clinic ID does not exist",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Clinic Not Found"),
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
    public function updateStatus(Request $request, $clinicId)
    {
        try {
            $response = $this->manageClinicService->updateStatus($request, $clinicId);
            return response()->json($response['data'], $response['status']);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/clinics/name/{name}",
     *   summary="Show Clinic",
     *   description="Admin can get a clinic's details by its name, Clinic can get it's own detail",
     *   operationId="clinicShowByName",
     *   security={ {"bearerAuth": {} }},
     *   tags={"clinic"},
     *   @OA\Parameter(
     *      name="name",
     *      description="Clinic Name",
     *      example="exampleName",
     *      required=false,
     *      in="path",
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Clinic Found",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Clinic Found"),
     *       @OA\Property(
     *         property="clinic",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="account_id", type="string", example="C999999"),
     *         @OA\Property(property="is_enabled", type="boolean", example=true),
     *         @OA\Property(property="name", type="string", example="exampleName"),
     *         @OA\Property(property="address", type="string", example="Address Line"),
     *         @OA\Property(property="city", type="string", example="Example City"),
     *         @OA\Property(property="state", type="string", example="Example State"),
     *         @OA\Property(property="zipcode", type="string", example="123456"),
     *         @OA\Property(property="user_id", type="integer", example="1"),
     *         @OA\Property(property="email", type="string", format="email")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="When the token is expired, Unauthenticated message will be sent, If clinic user passes a name in api unauthorized message is passed",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated."),
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Clinic Name does not exist",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Clinic Not Found"),
     *     )
     *   )
     * )
     */
    public function showByName(Request $request, $name = null)
    {
        try {
            return $this->manageClinicService->getClinicByName($name);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/clinics/download",
     *   summary="Get the list of clinics in csv format",
     *   description="Get list of all clinics in csv format with sort_by, sort_order, clinic name as params",
     *   operationId="clinicsDownload",
     *   security={ {"bearerAuth": {} }},
     *   tags={"clinic"},
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Search By clinic Name",
     *     required=false,
     *     @OA\Schema(
     *       type="string",
     *       default="downloadClinics"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Search By Clinic Name",
     *     required=false,
     *     @OA\Schema(
     *       type="string",
     *       default="clinic1"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="sort_by",
     *     in="query",
     *     description=" 'name', sort by a name",
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
    public function downloadList(Request $request)
    {
        try {
            $response = $this->manageClinicService->downloadList($request);
            if ($response['statusCode'] === 200) {
                return response($response['responseBody'], $response['statusCode'], [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="Clinics.csv"',
                ]);
            } else {
                return response($response['responseBody'], $response['statusCode']);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/clinics/dropdown",
     *     summary="Get the list of enabled clinic names",
     *     tags={"clinic"},
     *     security={ {"bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="string")
     *         )
     *     )
     * )
     */

    public function clinicDropdown(Request $request)
    {
        try {
            $output = $this->manageClinicService->getEnabledClinicNames($request);
            return response()->json($output);
        } catch (Exception $e) {
            return response()->json([
                'message' => $this->internalServerError
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}