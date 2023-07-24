<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use SoftDeletes;
    protected $table = 'clinics';

    protected $fillable = [
        'name',
        'account_id',
        'is_enabled',
        'address',
        'city',
        'state',
        'zipcode',
        'user_id'
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function getIsEnabledAttribute($value)
    {
        return (bool) $value;
    }
}
