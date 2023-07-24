<?php

namespace App\Services\ReactorCycle;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ReactorCycleService
{
    public $reactorCycleNotFoundMessage = 'Reactor Cycle not found.';
    /**
     * Handles logic for clinic store api call.
     *
     * @author Growexx
     * @param $request Request
     * @return array
     */
    public function store($request)
    {
        if (ReactorCycle::where('name', $request['cycle_name'])->exists()) {
            $responseBody = [
                'message' => 'Cycle Name has already been taken'
            ];
            $statusCode = Response::HTTP_BAD_REQUEST;
        } else {
            $reactorCycle = $this->addReactorCycle($request);
            $responseBody = [
                'message' => 'Reactor Cycle added.',
                'reactor-cycle' => $reactorCycle
            ];
            $statusCode = Response::HTTP_OK;
        }
        return [
            'responseBody' => $responseBody,
            'statusCode' => $statusCode
        ];
    }

    /**
     * Add a new ReactorCycle.
     *
     * @author Growexx
     * @param Request $request
     * @return array
     */
    public function addReactorCycle(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $reactor = Reactor::firstOrCreate([
                'name' => $request['reactor_name']
            ]);

            $reactorCycle = ReactorCycle::create([
                'name' => $request['cycle_name'],
                'reactor_id' => $reactor->id,
                'mass' => $request['mass'],
                'target_start_date' => date('Y-m-d', strtotime($request['target_start_date'])),
                'expiration_date' => date('Y-m-d', strtotime($request['expiration_date'])),
            ]);

            $reactorCycle = [
                'id' => $reactorCycle->id,
                'cycle_name' => $reactorCycle->name,
                'reactor_name' => $reactor->name,
                'mass' => $reactorCycle->mass,
                'target_start_date' => $reactorCycle->target_start_date,
                'expiration_date' => $reactorCycle->expiration_date,
                'is_enabled' => $reactorCycle->is_enabled,
            ];

            return $reactorCycle;
        });
    }

    /**
     * handle Get List of Reactor Cycles api call.
     *
     * @author Growexx
     * @param $request Request
     * @return array
     */
    public function index($request)
    {
        $perPage = $request->input('per_page', config('global.pagination.perPage'));
        $output = $this->reactorCycleList($request);
        $reactorCycles = $output['reactor-cycles'];
        if ($reactorCycles === null) {
            return $output;
        }
        $reactorCycles = $reactorCycles->paginate($perPage);
        $reactorCycles->withPath(env('APP_URL') . '/api/reactor-cycles');
        if ($reactorCycles->total() === 0) {
            $responseBody = [
                'message' => 'No Records Found.'
            ];
            $statusCode = Response::HTTP_OK;
        } else {
            $responseBody = [
                'reactor-cycles' => $reactorCycles
            ];
            $statusCode = Response::HTTP_OK;
        }
        return [
            'responseBody' => $responseBody,
            'statusCode' => $statusCode
        ];
    }

    /**
     * Get List of Reactor Cycles.
     *
     * @author Growexx
     * @param $request Request
     * @return array
     */
    public function reactorCycleList($request)
    {
        $name = $request->input('name', '');
        $sortBy = $request->input('sort_by', '');
        $sortOrder = $request->input('sort_order', 'asc');
        $reactorCycles = null;
        $allowedSortColumns = ['id', 'name', 'reactor_name', 'mass', 'target_start_date', 'expiration_date', ''];
        if (!in_array($sortBy, $allowedSortColumns)) {
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
            $reactorCycles = ReactorCycle::join('reactors', 'reactor_cycles.reactor_id', '=', 'reactors.id')
                ->where('reactor_cycles.name', 'like', '%' . $name . '%')
                ->select(
                    'reactor_cycles.id',
                    'reactor_cycles.name as name',
                    'reactors.name as reactor_name',
                    'reactor_cycles.mass',
                    'reactor_cycles.target_start_date',
                    'reactor_cycles.expiration_date',
                    'reactor_cycles.is_enabled'
                );
            if ($sortBy !== '') {
                $reactorCycles = $reactorCycles->orderBy($sortBy, $sortOrder);
            }
            $reactorCycles->orderBy('id', 'desc');
            $reactorCycleStatus = $request->input('status');
            if (!empty($reactorCycleStatus)) {
                $reactorCycleStatus = explode(',', $reactorCycleStatus);
                $reactorCycleStatus = array_map(function ($item) {
                    return ($item === 'enabled');
                }, $reactorCycleStatus);
                $reactorCycles = $reactorCycles->whereIn('is_enabled', $reactorCycleStatus);
            }
            $fromDate = $request->input('from_date', '');
            $toDate = $request->input('to_date', '');
            if($fromDate!=='') {
                $reactorCycles = $reactorCycles->where('target_start_date', '>=', date('Y-m-d', strtotime($fromDate)));
            }
            if($toDate!=='') {
                $reactorCycles = $reactorCycles->where('expiration_date', '<=', date('Y-m-d', strtotime($toDate)));
            }
        }
        return [
            'statusCode' => $statusCode,
            'responseBody' => $responseBody,
            'reactor-cycles' => $reactorCycles,
        ];
    }

    /**
     * Handles the enable-disable status of reactor-cycles.
     *
     * @author Growexx
     * @param $request
     * @param $reactorCycleId
     * @return array
     */
    public function updateStatus(Request $request, $reactorCycleId)
    {
        return DB::transaction(function () use ($request, $reactorCycleId) {
            $reactorCycle = ReactorCycle::find($reactorCycleId);
            if (!$reactorCycle) {
                return [
                    'status' => Response::HTTP_NOT_FOUND,
                    'data' => [
                        'message' => 'Reactor Cycle not found.'
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

            $reactorCycle->is_enabled = $request['is_enabled'];
            $reactorCycle->save();

            return [
                'status' => Response::HTTP_OK,
                'data' => [
                    'message' => 'Reactor Cycle Status Updated'
                ]
            ];
        });
    }

    /**
     * Handles logic for show reactor cycle api.
     *
     * @author Growexx
     * @param $reactorCycleId integer
     * @return array
     */
    public function show($reactorCycleId)
    {
        $reactorCycle = ReactorCycle::find($reactorCycleId);
        if ($reactorCycle) {
            $reactorCycle = [
                'id' => $reactorCycle->id,
                'cycle_name' => $reactorCycle->name,
                'reactor_name' => $reactorCycle->reactor->name,
                'mass' => $reactorCycle->mass,
                'target_start_date' => $reactorCycle->target_start_date,
                'expiration_date' => $reactorCycle->expiration_date,
                'is_enabled' => $reactorCycle->is_enabled,
            ];
            $responseBody = [
                'message' => 'Reactor Cycle Found',
                'reactor_cycle' => $reactorCycle
            ];
            $statusCode = Response::HTTP_OK;
        } else {
            $responseBody = [
                'message' => $this->reactorCycleNotFoundMessage
            ];
            $statusCode = Response::HTTP_NOT_FOUND;
        }
        return [
            'responseBody' => $responseBody,
            'statusCode' => $statusCode
        ];
    }

    /**
     * handles logic for update reactor-cycle.
     *
     * @author Growexx
     * @param $request Request, $reactorCycleId integer
     * @return array
     */
    public function update($request, $reactorCycleId)
    {
        $reactorCycle = ReactorCycle::find($reactorCycleId);
        if (!$reactorCycle) {
            $responseBody = [
                'message' => $this->reactorCycleNotFoundMessage
            ];
            $statusCode = Response::HTTP_NOT_FOUND;
        } else if ($reactorCycle->name !== $request['cycle_name'] && ReactorCycle::where('name', $request['cycle_name'])->exists()) {
            $responseBody = [
                'message' => 'Cycle Name has already been taken'
            ];
            $statusCode = Response::HTTP_BAD_REQUEST;
        } else {
            $this->editReactorCycle($request, $reactorCycle);
            $responseBody = [
                'message' => 'Reactor Cycle updated.',
            ];
            $statusCode = Response::HTTP_OK;
        }
        return [
            'responseBody' => $responseBody,
            'statusCode' => $statusCode
        ];
    }

    /**
     * edit a reactor cycle.
     *
     * @author Growexx
     * @param $reactorCycle ReactorCycle
     * @return void
     */
    public function editReactorCycle($request, $reactorCycle)
    {
        DB::transaction(function () use ($request, $reactorCycle) {
            $reactor = Reactor::firstOrCreate([
                'name' => $request['reactor_name']
            ]);

            $reactorCycle->name = $request['cycle_name'];
            $reactorCycle->reactor_id = $reactor->id;
            $reactorCycle->mass = $request['mass'];
            $reactorCycle->target_start_date = $request['target_start_date'];
            $reactorCycle->expiration_date = $request['expiration_date'];
            $reactorCycle->save();
        });
    }

    /**
     * Handles logic for delete reactor-cycle api.
     *
     * @author Growexx
     * @param $reactorCycleId integer
     * @return array
     */
    public function destroy($reactorCycleId)
    {
        $reactorCycle = ReactorCycle::find($reactorCycleId);
        if ($reactorCycle) {
            $this->deleteReactorCycle($reactorCycle);
            $responseBody = [
                'message' => 'Reactor cycle deleted.'
            ];
            $statusCode = Response::HTTP_OK;
        } else {
            $responseBody = [
                'message' => $this->reactorCycleNotFoundMessage
            ];
            $statusCode = Response::HTTP_NOT_FOUND;
        }
        return [
            'responseBody' => $responseBody,
            'statusCode' => $statusCode
        ];
    }

    /**
     * delete a reactor cycle.
     *
     * @author Growexx
     * @param $reactorCycle reactor cycle
     * @return void
     */
    public function deleteReactorCycle(ReactorCycle $reactorCycle)
    {
        DB::transaction(function () use ($reactorCycle) {
            $reactorCycle->delete();
        });
    }

    /**
     * Get the available reactor cycles for a given reactor name and injection date.
     *
     * @author Growexx
     * @param  string  $reactorName  The name of the reactor.
     * @param  string  $injectionDate  The injection date.
     * @param  string|null  $orderID  The order ID (optional).
     * @return array
     */
    public function getAvailable($reactorName, $injectionDate, $orderID = null)
    {
        if (!$reactorName || !$injectionDate) {
            $responseBody = [
                'message' => 'Missing required query parameter(s)'
            ];
            $responseCode = Response::HTTP_BAD_REQUEST;
        } else {
            $reactor = Reactor::where('name', $reactorName)->first();
            if (!$reactor) {
                $responseBody = [
                    'message' => 'Reactor does not exist'
                ];
                $responseCode = Response::HTTP_BAD_REQUEST;
            } else {
                $reactorCycle = $reactor->availReactorCycles($injectionDate, $orderID)->get();

                if ($reactorCycle->isEmpty()) {
                    $responseBody = [
                        'message' => 'No Reactor Cycle Exists'
                    ];
                    $responseCode = Response::HTTP_NOT_FOUND;
                } else {
                    $responseBody = [
                        'reactor-cycles' => $reactorCycle
                    ];
                    $responseCode = Response::HTTP_OK;
                }
            }
        }

        return response()->json($responseBody, $responseCode);
    }

    /**
     * Update the archived status of reactor cycles
     *
     * @author Growexx
     * @return void
     */
    public function updateArchivedStatus() {
        DB::transaction(function () {
            ReactorCycle::where(function($query) {
                $query->where('is_archived',true)
                ->where('is_enabled',true)
                ->where('expiration_date', '>=', Carbon::now()->format('Y-m-d'));
            })->update(['is_archived'=>false, 'archived_status'=>null]);

            ReactorCycle::where(function($query) {
                $query->where('is_archived',false)
                ->where('is_enabled',false);
            })->update(['is_archived'=>true, 'archived_status'=>'Disabled']);

            ReactorCycle::where(function($query) {
                $query->where('is_archived',false)
                ->where('expiration_date', '<', Carbon::now()->format('Y-m-d'));
            })->update(['is_archived'=>true, 'archived_status'=>'Expired']);
        });
    }

    /**
     * handle api logic to get reactor cycles
     *
     * @author Growexx
     * @param $request Request
     * @return array
     */
    public function getArchived($request)
    {
        $perPage = $request->input('per_page', config('global.pagination.perPage'));
        $name = $request->input('name', '');
        $this->updateArchivedStatus();
        $reactorCycle = ReactorCycle::join('reactors', 'reactor_cycles.reactor_id', '=', 'reactors.id')
            ->where('reactor_cycles.name', 'like', '%' . $name . '%')
            ->where('reactor_cycles.is_archived', true)
            ->select(
                'reactor_cycles.id',
                'reactor_cycles.name as cycle_name',
                'reactors.name as reactor_name',
                'reactor_cycles.mass',
                'reactor_cycles.target_start_date',
                'reactor_cycles.expiration_date',
                'reactor_cycles.is_enabled',
                'reactor_cycles.archived_status'
            );
        $reactorCycle = $reactorCycle->paginate($perPage);
        $reactorCycle->withPath(env('APP_URL') . '/api/reactor-cycles/archived');
        if ($reactorCycle->total() === 0) {
            $responseBody = [
                'message' => 'No Archived Reactor Cycles Exist'
            ];
        } else {
            $responseBody = [
                'reactor-cycles' => $reactorCycle
            ];
        }
        return [
            'responseBody' => $responseBody,
            'responseCode' => Response::HTTP_OK
        ];
    }
}