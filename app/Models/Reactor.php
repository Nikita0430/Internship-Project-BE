<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Reactor extends Model
{
    protected $table = 'reactors';

    protected $fillable = [
        'name'
    ];

    public $timestamps = false;

    public function reactorCycles(){
        return $this->hasMany(ReactorCycle::class);
    }

    public function availReactorCycles($date, $orderID){
        $reactorCycles = $this->reactorCycles()
        ->where('is_enabled', true)
        ->where('mass', '>', 0)
        ->where('target_start_date', '<=', $date)
        ->where('expiration_date', '>=', $date)
        ->orderBy('expiration_date');
        
        if($orderID !== null && (Auth::user()->isAdmin() || in_array($orderID, Auth::user()->clinic->orders->pluck('id')->toArray()))){
            $order = Order::find($orderID);
            $reactorCycle = ReactorCycle::withTrashed()->where('id', $order->reactor_cycle_id);
            $reactorCycles = $reactorCycles->union($reactorCycle);
        }
        
        return $reactorCycles;
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
