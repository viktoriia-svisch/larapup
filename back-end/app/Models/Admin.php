<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
class Admin extends Authenticate implements JWTSubject
{
    use Notifiable;
    protected $table = 'Admins';
    protected $hidden = [
        'password', 'deleted_at'
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function setPasswordAttribute($value){
        return $this->attributes['password'] = Hash::make($value);
    }
}
