<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
class Student extends Authenticate implements JWTSubject
{
    use Notifiable;
    protected $table = 'students';
    protected $fillable = [
        'name', 'email', 'password',
    ];
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
    public function setPasswordAttribute($value)
    {
        return $this->attributes['password'] = Hash::make($value);
    }
    public function article()
    {
        return $this->hasMany(Article::class);
    }
    public function faculty_student()
    {
        return $this->hasMany(FacultyStudent::class);
    }
}
