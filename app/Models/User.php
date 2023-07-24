<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'email_verified_at',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /**
     * get the clinic associated with this user
     *
     * @author growexx
     * @return Clinic::class
     */
    public function clinic()
    {
        return $this->hasOne(Clinic::class);
    }

    /**
     * check if this user is admin or not
     *
     * @author growexx
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * check if this user is enabled or not
     *
     * @author growexx
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isAdmin() || $this->clinic->is_enabled;
    }

     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @author growexx
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @author growexx
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
