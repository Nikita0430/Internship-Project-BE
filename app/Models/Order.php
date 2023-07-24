<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'clinic_id',
        'order_no',
        'email',
        'placed_at',
        'confirmed_at',
        'shipped_at',
        'out_for_delivery_at',
        'delivered_at',
        'cancelled_at',
        'injection_date',
        'dog_name',
        'dog_breed',
        'dog_age',
        'dog_weight',
        'dog_gender',
        'no_of_elbows',
        'dosage_per_elbow',
        'total_dosage',
        'reactor_id',
        'reactor_cycle_id',
        'status',
        'order_instructions',
    ];

    public $timestamps = false;

    public function clinic(){
        return $this->belongsTo(Clinic::class)->withTrashed();
    }

    public function reactor(){
        return $this->belongsTo(Reactor::class);
    }

    public function notifications(){
        return $this->hasMany(Notification::class);
    }

    public function reactorCycle(){
        return $this->belongsTo(ReactorCycle::class);
    }
}
