<?php

namespace App\Services\Order;

use App\Models\Clinic;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\Notification;

class OrderService
{
    public $OrderNotFoundMessage = 'Order not found.';
    /**
     * Store a new order.
     *
     * @author Growexx
     * @param  $request 
     * @return array 
     */
    public function store($request)
    {
        return DB::transaction(function () use ($request) {
            $clinic = Clinic::find($request->clinic_id);
            $reactor = Reactor::where('name', $request->reactor_name)->first();
            $reactorCycle = ReactorCycle::find($request->reactor_cycle_id);
            $totalDosage = $request->no_of_elbows * $request->dosage_per_elbow;

            if (!Auth::user()->isAdmin() && Auth::user()->clinic->id != $request->clinic_id) {
                $responseBody = [
                    'message' => Auth::user()->clinic->id
                ];
                $responseCode = Response::HTTP_UNAUTHORIZED;
            } elseif (!$clinic) {
                $responseBody = [
                    'message' => 'Clinic Not Found'
                ];
                $responseCode = Response::HTTP_BAD_REQUEST;
            } elseif (!$reactor) {
                $responseBody = [
                    'message' => 'Reactor Not Found'
                ];
                $responseCode = Response::HTTP_BAD_REQUEST;
            } elseif (!$reactorCycle) {
                $responseBody = [
                    'message' => 'Reactor Cycle Not Found'
                ];
                $responseCode = Response::HTTP_BAD_REQUEST;
            } elseif ($reactorCycle->reactor->id !== $reactor->id) {
                $responseBody = [
                    'message' => 'Reactor does not match selected reactor cycle'
                ];
                $responseCode = Response::HTTP_BAD_REQUEST;
            } elseif (!$reactorCycle->isDosageAvailable($request->injection_date, $totalDosage)) {
                $responseBody = [
                    'message' => 'Reactor cycle is unavailable for injection date and total dosage'
                ];
                $responseCode = Response::HTTP_BAD_REQUEST;
            } else {
                $reactorCycle->mass = $reactorCycle->mass - $totalDosage;
                $reactorCycle->save();
                $order = new Order();
                $order->order_no = 'WEBO' . sprintf('%04d', $order->id);
                $order->clinic_id = $request->clinic_id;
                $order->email = $request->email;
                $order->placed_at = Carbon::now()->toDateTimeString();
                $order->shipped_at = Carbon::now()->addDay()->toDateTimeString();
                $order->injection_date = $request->injection_date;
                $order->dog_name = $request->dog_name;
                $order->dog_breed = $request->dog_breed;
                $order->dog_age = $request->dog_age;
                $order->dog_weight = $request->dog_weight;
                $order->dog_gender = $request->dog_gender;
                $order->no_of_elbows = $request->no_of_elbows;
                $order->dosage_per_elbow = $request->dosage_per_elbow;
                $order->total_dosage = $totalDosage;
                $order->reactor_id = $reactor->id;
                $order->reactor_cycle_id = $reactorCycle->id;
                $order->order_instructions = $request->order_instructions;
                $order->save();
                $order->order_no = 'WEBO' . sprintf('%04d', $order->id);
                $order->save();

                Notification::create([
                    'status_change' => config('global.orders.status.Pending'),
                    'order_id' => $order->id,
                ]);

                $responseBody = [
                    'message' => 'Order Placed',
                ];
                $responseCode = Response::HTTP_OK;
            }

            return [
                'responseBody' => $responseBody,
                'statusCode' => $responseCode
            ];
        });
    }

    /**
     * Handle the list of order api request
     *
     * @author Growexx
     * @param  $request 
     * @return array 
     */
    public function index($request)
    {
        $perPage = $request->input('per_page', config('global.pagination.perPage'));
        $output = $this->orderList($request);
        $orders = $output['orders'];
        if ($orders === null) {
            return $output;
        }
        $orders = $orders->paginate($perPage);
        $orders->withPath(env('APP_URL') . '/api/orders');
        if ($orders->total() === 0) {
            $responseBody = [
                'message' => 'No Records Found'
            ];
            $responseCode = Response::HTTP_OK;
        } else {
            $responseBody = [
                'orders' => $orders
            ];
            $responseCode = Response::HTTP_OK;
        }
        return [
            'responseBody' => $responseBody,
            'responseCode' => $responseCode
        ];
    }

    /**
     * Handle the export orders api call
     *
     * @author Growexx
     * @param  $request 
     * @return array 
     */
    public function downloadList($request)
    {
        $output = $this->orderList($request);
        $orders = $output['orders'];
        if ($orders === null) {
            return $output;
        }
        $orders = $orders->get();
        $csvContent = implode(',', [
            'Order #',
            'Clinic Name',
            'Dog Name',
            'Dog Breed',
            'Order Date',
            'Ship Date',
            'Dosage Per Elbow',
            'Total Dosage',
            'Injection Date',
            'Status'
        ]) . "\n";
        if ($orders->isEmpty()) {
            $csvContent .= "No record found\n";
        }
        $dateFormat = 'd F Y';
        foreach ($orders as $order) {
            $csvContent .= implode(',', [
                $order['order_no'],
                $order['clinic_name'],
                $order['dog_name'],
                $order['dog_breed'],
                "\"" . Carbon::create($order['placed_at'])->format($dateFormat) . "\"",
                "\"" . Carbon::create($order['shipped_at'])->format($dateFormat) . "\"",
                $order['dosage_per_elbow'],
                $order['total_dosage'],
                "\"" . Carbon::create($order['injection_date'])->format($dateFormat) . "\"",
                Str::title($order['status'])
            ]) . "\n";
        }
        return [
            'responseBody' => $csvContent,
            'responseCode' => 200
        ];
    }

    /**
     * Get list of all orders with search and sort parameters
     *
     * @author Growexx
     * @param  $request 
     * @return array 
     */
    private function orderList($request)
    {
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', '');
        $sortOrder = $request->input('sort_order', 'asc');
        $orders = null;
        $allowedSortColumns = ['order_no', 'clinic_name', 'placed_at', 'shipped_at', 'dosage_per_elbow', 'total_dosage', ''];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $responseBody = [
                'message' => 'Invalid sort by parameter'
            ];
            $responseCode = Response::HTTP_BAD_REQUEST;
        } elseif ($sortOrder !== 'asc' && $sortOrder !== 'desc') {
            $responseBody = [
                'message' => 'Invalid sort order parameter'
            ];
            $responseCode = Response::HTTP_BAD_REQUEST;
        } else {
            $responseCode = 200;
            $responseBody = [
                'message' => 'Success'
            ];
            $orders = Order::query()->where(function ($query) use ($search) {
                $query->where('dog_name', 'like', '%' . $search . '%')
                    ->orWhere('dog_breed', 'like', '%' . $search . '%')
                    ->orWhere(function ($query) use ($search) {
                        $query->whereIn('clinic_id', function ($subquery) use ($search) {
                            $subquery->select('id')
                                ->from('clinics')
                                ->where('name', 'like', '%' . $search . '%');
                        });
                    });
            });
            if (!Auth::user()->isAdmin()) {
                $orders = $orders->where('clinic_id', '=', Auth::user()->clinic->id);
            }
            $orders = $orders->join('clinics', 'orders.clinic_id', '=', 'clinics.id')
                ->join('reactors', 'orders.reactor_id', '=', 'reactors.id')
                ->select(
                    'orders.id as order_id',
                    'orders.order_no',
                    'orders.clinic_id as clinic_id',
                    'clinics.name as clinic_name',
                    'clinics.account_id',
                    'clinics.address',
                    'clinics.city',
                    'clinics.state',
                    'clinics.zipcode',
                    'orders.reactor_id as reactor_id',
                    'reactors.name as reactor_name',
                    'orders.reactor_cycle_id as reactor_cycle_id',
                    'orders.email',
                    'orders.placed_at',
                    'orders.confirmed_at',
                    'orders.shipped_at',
                    'orders.out_for_delivery_at',
                    'orders.delivered_at',
                    'orders.cancelled_at',
                    'orders.injection_date',
                    'orders.dog_name',
                    'orders.dog_breed',
                    'orders.dog_age',
                    'orders.dog_weight',
                    'orders.dog_gender',
                    'orders.no_of_elbows',
                    'orders.dosage_per_elbow',
                    'orders.total_dosage',
                    'orders.status',
                    'orders.order_instructions'
                );
            if ($sortBy !== '') {
                $orders = $orders->orderBy($sortBy, $sortOrder);
            }
            $orders = $orders->orderBy('placed_at', 'desc');
            $orderStatus = $request->input('status');
            if (!empty($orderStatus)) {
                $orderStatus = explode(',', $orderStatus);
                $orders = $orders->whereIn('orders.status', $orderStatus);
            }
        }
        return [
            'responseCode' => $responseCode,
            'responseBody' => $responseBody,
            'orders' => $orders,
        ];
    }

    /**
     * update the status of orders
     *
     * @author Growexx
     * @param  $request Request, $orderId number
     * @return array 
     */
    public function updateStatus($request, $orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            $responseBody = [
                'message' => 'Order Not Found'
            ];
            $responseCode = Response::HTTP_NOT_FOUND;
        } elseif ($order->status === config('global.orders.status.Delivered') || $this->orderMap[$order->status] >= $this->orderMap[$request->status]) {
            $responseBody = [
                'message' => 'Status cannot be changed'
            ];
            $responseCode = Response::HTTP_BAD_REQUEST;
        } else {
            $this->changeStatus($request->status, $order);
            $responseBody = [
                'message' => 'Order Status Updated'
            ];
            $responseCode = Response::HTTP_OK;
        }
        return [
            'responseBody' => $responseBody,
            'responseCode' => $responseCode
        ];
    }

    /**
     * Bulk update the status of orders
     *
     * @author Growexx
     * @param  $request 
     * @return array 
     */
    public function updateStatusBulk($request)
    {
        $responseBody = [
            'message' => 'Order Status Updated'
        ];
        $responseCode = Response::HTTP_OK;
        $orders = $request->orders;
        foreach ($orders as $orderId) {
            $order = Order::find($orderId);
            if (!$order) {
                $responseBody = [
                    'message' => 'One or more orders not found'
                ];
                $responseCode = Response::HTTP_BAD_REQUEST;
            } elseif ($order->status === config('global.orders.status.Delivered') || $this->orderMap[$order->status] >= $this->orderMap[$request->status]) {
                $responseBody = [
                    'message' => 'Status cannot be changed for one or more orders'
                ];
                $responseCode = Response::HTTP_BAD_REQUEST;
            }
            if ($responseCode !== Response::HTTP_OK) {
                return [
                    'responseBody' => $responseBody,
                    'responseCode' => $responseCode
                ];
            }
        }
        foreach ($orders as $orderId) {
            $order = Order::find($orderId);
            $this->changeStatus($request->status, $order);
        }
        return [
            'responseBody' => $responseBody,
            'responseCode' => $responseCode
        ];
    }

    private $orderMap = [
        'pending' => 0,
        'confirmed' => 1,
        'shipped' => 2,
        'out for delivery' => 3,
        'delivered' => 4,
        'cancelled' => 5
    ];
    private $dateColumns = ['placed_at', 'confirmed_at', 'shipped_at', 'out_for_delivery_at', 'delivered_at', 'cancelled_at'];

    /**
     * Change the status of orders
     *
     * @author Growexx
     * @param  $request 
     * @return void 
     */
    public function changeStatus($status, $order)
    {
        DB::transaction(function () use ($status, $order) {
            $order->status = $status;
            Mail::to($order->clinic->user->email)->send(new OrderStatusMail($order));
            Notification::create([
                'status_change' => $status,
                'order_id' => $order->id,
            ]);
            if ($status === config('global.orders.status.Cancelled')) {
                $order->cancelled_at = Carbon::now()->toDateTimeString();
            } else {
                foreach ($this->dateColumns as $key => $dateColumn) {
                    if ($key === 5 || $key > $this->orderMap[$status]) {
                        break;
                    } elseif ($key === $this->orderMap[$status] || $order[$dateColumn] === null) {
                        $order[$dateColumn] = Carbon::now()->toDateTimeString();
                    }
                }
            }
            $order->save();
        });
    }

    /**
     * Handles logic for show order api.
     *
     * @author Growexx
     * @param $orderId integer
     * @return array
     */
    public function show($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            $responseBody = [
                'message' => $this->OrderNotFoundMessage,
            ];
            $statusCode = Response::HTTP_NOT_FOUND;
        } else if (!Auth::user()->isAdmin() && $order->clinic->user_id != Auth::user()->id) {
            $responseBody = [
                'message' => 'Unauthorized'
            ];
            $statusCode = Response::HTTP_UNAUTHORIZED;
        } else {
            $responseBody = [
                'message' => 'Order Found',
                'order' => [
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'clinic_id' => $order->clinic_id,
                    'clinic_name' => $order->clinic->name,
                    'account_id' => $order->clinic->account_id,
                    'address' => $order->clinic->address,
                    'city' => $order->clinic->city,
                    'state' => $order->clinic->state,
                    'zipcode' => $order->clinic->zipcode,
                    'reactor_id' => $order->reactor_id,
                    'reactor_name' => $order->reactor->name,
                    'reactor_cycle_id' => $order->reactor_cycle_id,
                    'email' => $order->email,
                    'placed_at' => $order->placed_at,
                    'confirmed_at' => $order->confirmed_at,
                    'shipped_at' => $order->shipped_at,
                    'out_for_delivery_at' => $order->out_for_delivery_at,
                    'delivered_at' => $order->delivered_at,
                    'cancelled_at' => $order->cancelled_at,
                    'injection_date' => $order->injection_date,
                    'dog_name' => $order->dog_name,
                    'dog_breed' => $order->dog_breed,
                    'dog_age' => $order->dog_age,
                    'dog_weight' => $order->dog_weight,
                    'dog_gender' => $order->dog_gender,
                    'no_of_elbows' => $order->no_of_elbows,
                    'dosage_per_elbow' => $order->dosage_per_elbow,
                    'total_dosage' => $order->total_dosage,
                    'status' => $order->status,
                    'order_instructions' => $order->order_instructions,
                ]
            ];
            $statusCode = Response::HTTP_OK;
        }

        return [
            'responseBody' => $responseBody,
            'statusCode' => $statusCode,
        ];
    }
}