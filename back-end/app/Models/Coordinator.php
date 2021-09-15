<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Database\Eloquent\Model;

class Coordinator extends Authenticate implements JWTSubject
{

    use Notifiable;

    protected $table = 'coordinators';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'deleted_at'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setPasswordAttribute($value){
        return $this->attributes['password'] = Hash::make($value);
    }

    public function faculty_coordinator(){
        return $this->hasMany(FacultyCoordinator::class);
    }
}
