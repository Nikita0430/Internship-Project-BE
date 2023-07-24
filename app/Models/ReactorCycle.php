<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReactorCycle extends Model
{
    use SoftDeletes;
    protected $table = 'reactor_cycles';

    protected $fillable = [
        'name',
        'reactor_id',
        'mass',
        'target_start_date',
        'expiration_date',
        'is_enabled',
        'is_archived'
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public $timestamps = false;

    public function reactor(){
        return $this->belongsTo(Reactor::class);
    }

    public function isAvailable($date){
        return ($this->is_enabled) && ($this->mass>0) && ($this->target_start_date <= $date) && ($this->expiration_date >= $date);
    }

    public function isDosageAvailable($date, $totalDosage){
        return $this->isAvailable($date) && $this->mass>=$totalDosage;
    }
}
