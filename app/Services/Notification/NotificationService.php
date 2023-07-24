<?php
namespace App\Services\Notification;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class NotificationService {

    /**
     * get list of notifications
     *
     * @author Growexx
     * @return array 
     */
    public function index(){
        if(Auth::user()->isAdmin()){
            $responseBody = [
                'message' => 'Admin Cannot Get Notifications'
            ];
            $responseCode = Response::HTTP_UNAUTHORIZED;
        } else {
            $notifications = Notification::whereIn('order_id', Auth::user()->clinic->orders()->pluck('id'))->orderByDesc('created_at')->orderByDesc('is_seen')->join('orders', 'orders.id', '=', 'notifications.order_id')->select(
                'notifications.id as id',
                'notifications.order_id as order_id',
                'notifications.status_change as status_change',
                'notifications.is_seen as is_seen',
                'orders.order_no as order_no'
            );
            $notificationsResponse = $notifications->get();
            $unreadCount = $notifications->where('is_seen',false)->count();
            $responseBody = [
                'message' => 'Success',
                'unread_count' => $unreadCount,
                'notifications' => $notificationsResponse
            ];
            $responseCode = Response::HTTP_OK;
            if($notificationsResponse->count() === 0){
                $responseBody = [
                    'message' => 'No Notifications Found'
                ];
                $responseCode = Response::HTTP_OK;
            }
        }
        return [
            'responseBody' => $responseBody,
            'responseCode' => $responseCode
        ];
    }

    /**
     * update notifications to seen
     *
     * @author Growexx
     * @return array 
     */
    public function updateSeen(){
        return DB::transaction(function () {
            if(Auth::user()->isAdmin()){
                $responseBody = [
                    'message' => 'Admin Does Not Have Notifications'
                ];
                $responseCode = Response::HTTP_UNAUTHORIZED;
            } else {
                Notification::whereIn('order_id', Auth::user()->clinic->orders()->pluck('id'))->where('is_seen',false)->update(['is_seen' => true]);
                $responseBody = [
                    'message' => 'Notification Status Updated'
                ];
                $responseCode = Response::HTTP_OK;
            }
            return [
                'responseBody' => $responseBody,
                'responseCode' => $responseCode
            ];
        });
    }
}