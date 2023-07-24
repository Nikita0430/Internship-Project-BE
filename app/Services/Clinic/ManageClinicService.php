<?php

namespace App\Services\Clinic;


use App\Mail\NewUserMail;
use App\Models\Clinic;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;

class ManageClinicService
{
    public $clinicNotFoundMessage = 'Clinic not found.';
    /**
     * Handles logic for clinic store api call.
     *
     * @author Growexx
     * @param $request Request
     * @return array
     */
    public function store($request)
    {
        if (Clinic::where('name', $request['name'])->exists()) {
            $responseBody = [
                'message' => 'Clinic name has already been taken.'
            ];
            $statusCode = Response::HTTP_BAD_REQUEST;
        } else if (User::where('email', $request['email'])->exists()) {
            $responseBody = [
                'message' => 'Email has already been taken.'
            ];
            $statusCode = Response::HTTP_BAD_REQUEST;
        } else {
            $clinic = $this->addClinic($request);
            $responseBody = [
                'message' => 'Clinic added.',
                'clinic' => $clinic
            ];
            $statusCode = Response::HTTP_OK;
        }
        return [
            'responseBody' => $responseBody,
            'statusCode' => $statusCode
        ];
    }

    /**
     * Add a new clinic.
     *
     * @author Growexx
     * @param $request Request
     * @return array
     */
    public function addClinic($request)
    {
        return DB::transaction(function () use ($request) {
            $user = new User();
            $user->email = $request['email'];
            $user->password = Hash::make($request['password']);
            $user->role = 'clinic';
            $user->save();

            $clinic = new Clinic();
            $clinic->name = $request['name'];
            $clinic->is_enabled = true;
            $clinic->address = $request['address'];
            $clinic->city = $request['city'];
            $clinic->state = $request['state'];
            $clinic->zipcode = $request['zipcode'];
            $maxId = Clinic::withTrashed()->max('id');
            $clinic->account_id = 'C' . Str::padLeft($maxId + 1, 6, '0');
            $clinic->user_id = $user->id;
            $clinic->save();
            Mail::to($user->email)->send(new NewUserMail($user, $request['password']));

            return $clinic;
        });
    }

    /**
     * handle Get List of Clinics api call.
     *
     * @author Growexx
     * @param $request Request
     * @return array
     */
    public function index($request)
    {
        $perPage = $request->input('per_page', config('global.pagination.perPage'));
        $output = $this->clinicList($request);
        $clinics = $output['clinics'];
        if ($clinics === null) {
            return $output;
        }
        $clinics = $clinics->paginate($perPage);
        $clinics->withPath(env('APP_URL') . '/api/clinics');
        if ($clinics->total() === 0) {
            $responseBody = [
                'message' => 'No Records Found.'
            ];
            $statusCode = Response::HTTP_OK;
        } else {
            $responseBody = [
                'clinics' => $clinics
            ];
            $statusCode = Response::HTTP_OK;
        }
        return [
            'responseBody' => $responseBody,
            'statusCode' => $statusCode
        ];
    }

    /**
     * Get List of Clinics.
     *
     * @author Growexx
     * @param $request Request
     * @return array
     */
    public function clinicList($request)
    {
        $name = $request->input('name', '');
        $sortBy = $request->input('sort_by', '');
        $sortOrder = $request->input('sort_order', 'asc');
        $clinics = null;
        if ($sortBy !== 'name' && $sortBy !== '') {
            $responseBody = [
                'message' => 'Invalid sort by parameter.'
            ];
            $statusCode = Response::HTTP_BAD_REQUEST;
        } elseif ($sortOrder !== 'asc' && $sortOrder !== 'desc') {
            $responseBody = [
                'message' => 'Invalid sort order parameter.'
            ];
            $statusCode = Response::HTTP_BAD_REQUEST;
        } else {
            $statusCode = 200;
            $responseBody = [
                'message' => 'Success'
            ];
            $clinics = Clinic::where('name', 'like', '%' . $name . '%');
            if ($sortBy !== '') {
                $clinics = $clinics->orderBy($sortBy, $sortOrder);
            }
            $clinics->orderBy('id', 'desc');
            $clinicStatus = $request->input('status');
            if (!empty($clinicStatus)) {
                $clinicStatus = explode(',', $clinicStatus);
                $clinicStatus = array_map(function ($item) {
                    return ($item === 'enabled');
                }, $clinicStatus);
                $clinics = $clinics->whereIn('is_enabled', $clinicStatus);
            }
        }
        return [
            'statusCode' => $statusCode,
            'responseBody' => $responseBody,
            'clinics' => $clinics,
        ];
    }

    /**
     * handle Get List of Clinics in csv format API call.
     *
     * @author Growexx
     * @param $request Request
     * @return array
     */
    public function downloadList($request)
    {
        $response = $this->clinicList($request);
        $clinics = $response['clinics'];
        if ($clinics === null) {
            return $response;
        }
        $clinics = $clinics->get();
        $csvContent = implode(',', ['Name', 'Address', 'City', 'State', 'Zipcode', 'Status']) . "\n";
        if ($clinics->isEmpty()) {
            $csvContent .= "No record found\n";
        }
        foreach ($clinics as $clinic) {
            $status = $clinic->is_enabled ? 'Enabled' : 'Disabled';
            $csvContent .= implode(',', [$clinic['name'], $clinic['address'], $clinic['city'], $clinic['state'], $clinic['zipcode'], $status]) . "\n";
        }
        return [
            'statusCode' => 200,
            'responseBody' => $csvContent
        ];
    }

    /**
     * Handles the enable-disable status of clinics.
     *
     * @author Growexx
     * @param $request
     * @param $clinicId
     * @return array
     */
    public function updateStatus(Request $request, $clinicId)
    {
        return DB::transaction(function () use ($request, $clinicId) {
            $clinic = Clinic::find($clinicId);
            if (!$clinic) {
                return [
                    'status' => Response::HTTP_NOT_FOUND,
                    'data' => [
                        'message' => 'Clinic not found.'
                    ]
                ];
            }

            $validator = Validator::make($request->all(), [
                'is_enabled' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'data' => [
                        'message' => 'Validation failed.',
                        'errors' => $validator->errors()
                    ]
                ];
            }

            $clinic->is_enabled = $request['is_enabled'];
            $clinic->save();

            return [
                'status' => Response::HTTP_OK,
                'data' => [
                    'message' => 'Clinic Status Updated'
                ]
            ];
        });
    }

    /**
     * Handles logic for show clinic api.
     *
     * @author Growexx
     * @param $clinicId integer
     * @return array
     */
    public function show($clinicId)
    {
        $clinic = Clinic::find($clinicId);
        if ($clinic) {
            $clinic->user;
            $responseBody = [
                'message' => 'Clinic Found',
                'clinic' => $clinic
            ];
            $statusCode = Response::HTTP_OK;
        } else {
            $responseBody = [
                'message' => $this->clinicNotFoundMessage
            ];
            $statusCode = Response::HTTP_NOT_FOUND;
        }
        return [
            'responseBody' => $responseBody,
            'statusCode' => $statusCode
        ];
    }

    /**
     * Handles logic for delete clinic api.
     *
     * @author Growexx
     * @param $clinicId integer
     * @return array
     */
    public function destroy($clinicId)
    {
        $clinic = Clinic::find($clinicId);
        if ($clinic) {
            $this->deleteClinic($clinic);
            $responseBody = [
                'message' => 'Clinic deleted.'
            ];
            $statusCode = Response::HTTP_OK;
        } else {
            $responseBody = [
                'message' => $this->clinicNotFoundMessage
            ];
            $statusCode = Response::HTTP_NOT_FOUND;
        }
        return [
            'responseBody' => $responseBody,
            'statusCode' => $statusCode
        ];
    }

    /**
     * delete a clinic.
     *
     * @author Growexx
     * @param $clinic Clinic
     * @return void
     */
    public function deleteClinic(Clinic $clinic)
    {
        DB::transaction(function () use ($clinic) {
            $clinic->delete();
        });
    }

    /**
     * handles logic for update clinic.
     *
     * @author Growexx
     * @param $request Request, $clinicId integer
     * @return array
     */
    public function update($request, $clinicId)
    {
        $clinic = Clinic::find($clinicId);
        if (!$clinic) {
            $responseBody = [
                'message' => $this->clinicNotFoundMessage
            ];
            $statusCode = Response::HTTP_NOT_FOUND;
        } else if ($clinic->name !== $request['name'] && Clinic::where('name', $request['name'])->exists()) {
            $responseBody = [
                'message' => 'Clinic name has already been taken.'
            ];
            $statusCode = Response::HTTP_BAD_REQUEST;
        } else {
            $this->editClinic($request, $clinic);
            $responseBody = [
                'message' => 'Clinic updated.'
            ];
            $statusCode = Response::HTTP_OK;
        }
        return [
            'responseBody' => $responseBody,
            'statusCode' => $statusCode
        ];
    }

    /**
     * edit a clinic.
     *
     * @author Growexx
     * @param $clinic Clinic
     * @return void
     */
    public function editClinic($request, $clinic)
    {
        DB::transaction(function () use ($request, $clinic) {
            $clinic->name = $request['name'];
            $clinic->address = $request['address'];
            $clinic->city = $request['city'];
            $clinic->state = $request['state'];
            $clinic->zipcode = $request['zipcode'];
            $clinic->save();
        });
    }

    /**
     * Get the enabled clinic names for the dropdown.
     *
     * @author Growexx
     * @return array
     */
    public function getEnabledClinicNames($request)
    {
        $name = $request->input('name', '');
        $clinicNames = Clinic::where('is_enabled', true);
        if (!empty($name)) {
            $clinicNames = Clinic::where('is_enabled', true)->where('name', 'like', '%' . $name . '%');
            if ($clinicNames->count() === 0) {
                return ["No Matching Records Found"];
            }
            return $clinicNames->pluck('name');
        }
        return $clinicNames->pluck('name');
    }

    /**
     * Get the clinic information by name.
     *
     * @author Growexx
     * @param  string|null  $name  The name of the clinic (optional).
     * @return array
     */
    public function getClinicByName($name)
    {
        if (!Auth::user()->isAdmin()) {
            if ($name === null) {
                $clinic = Auth::user()->clinic;
            } else {
                return response()->json([
                    'message' => 'Unauthorized'
                ], Response::HTTP_UNAUTHORIZED);
            }
        } else {
            $clinic = Clinic::where('name', $name)->first();
        }

        if (!$clinic) {
            return response()->json([
                'message' => $this->clinicNotFoundMessage
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Clinic Found',
            'clinic' => [
                'id' => $clinic->id,
                'account_id' => $clinic->account_id,
                'name' => $clinic->name,
                'address' => $clinic->address,
                'city' => $clinic->city,
                'state' => $clinic->state,
                'zipcode' => $clinic->zipcode,
                'is_enabled' => $clinic->is_enabled,
                'user_id' => $clinic->user_id,
                'email' => $clinic->user->email,
            ]
        ], Response::HTTP_OK);
    }
}