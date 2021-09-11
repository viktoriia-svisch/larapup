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
    public function faculty_coordinator(){
        return $this->hasMany(FacultyCoordinator::class);
    }
}
