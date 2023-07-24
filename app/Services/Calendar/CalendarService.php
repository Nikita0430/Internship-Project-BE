<?php
namespace App\Services\Calendar;
use App\Models\Order;
use App\Models\Reactor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CalendarService
{
    /**
     * Get List of Reactors for calendar view dropdown.
     *
     * @author Growexx
     * @return array
     */
    public function getReactorList() {
        $reactorList = Reactor::orderBy('id')->get();
        $reactorNames = [];
        foreach ($reactorList as $reactor) {
            array_push($reactorNames,$reactor->name);
        }
        $defaultReactor = $this->defaultReactor($reactorNames);
        return [
            'responseBody' => [
                'reactors' => $reactorNames,
                'defaultReactor' => $defaultReactor
            ],
            'responseCode' => Response::HTTP_OK
        ];
    }

    /**
     * Get the default reactor that should be selected on calendar view
     *
     * @author Growexx
     * @param $reactorNames array
     * @return string
     */
    private function defaultReactor($reactorNames) {
        $user = Auth::user();
        $defaultReactor = $reactorNames[0];
        if(!$user->isAdmin()){
            $order = Order::where('clinic_id', $user->clinic->id)->orderBy('placed_at', 'desc');
            if($order->exists()) {
                $defaultReactor = $order->first()->reactor->name;
            }
        }
        return $defaultReactor;
    }

    /**
     * handle api logic for getting the availabilities of selected reactor in selected month and year.
     *
     * @author Growexx
     * @param $request Request
     * @return array
     */
    public function getReactorAvail($request) {
        $reactor = Reactor::where('name',$request['reactor_name'])->first();
        if(!$reactor){
            $responseBody = [
                'message' => 'No reactors found.'
            ];
            $responseCode = Response::HTTP_BAD_REQUEST;
        } else {
            $dates = $this->getDatesOfMonth($request['month'], $request['year']);
            $calendar = $this->getAvail($dates, $reactor);
            $responseBody = [
                'message' => 'Success',
                'calendar' => $calendar
            ];
            $responseCode = Response::HTTP_OK;
        }
        return [
            'responseBody' => $responseBody,
            'responseCode' => $responseCode
        ];
    }

    /**
     * Get lists of dates in current month.
     *
     * @author Growexx
     * @param $month string, $year string
     * @return CarbonPeriod
     */ 
    private function getDatesOfMonth($month, $year) {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        return CarbonPeriod::create($startDate, $endDate);
    }

    /**
     * Get availability for each date for the reactor.
     *
     * @author Growexx
     * @param $dates array, $reactor Reactor
     * @return array
     */
    private function getAvail($dates, $reactor) {
        $reactorCycles = $reactor->reactorCycles;
        $calendar = [];
        foreach ($dates as $date) {
            $date = $date->toDateString();
            if($date <= Carbon::today()->toDateString()) {
                continue;
            }
            $is_available = false;
            foreach ($reactorCycles as $cycle) {
                if ($cycle->isAvailable($date)) {
                    $is_available = true;
                    break;
                }
            }
            $calendar[] = [
                'date' => $date,
                'is_available' => $is_available
            ];
        }
        return $calendar;
    }
}