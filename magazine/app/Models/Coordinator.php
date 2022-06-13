<?php
namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
class Coordinator extends Authenticate
{
    use Notifiable, SoftDeletes;
    protected $table = 'coordinators';
    protected $hidden = [
        'password', 'deleted_at'
    ];
    protected $fillable = [
        'email', 'password', 'first_name', 'last_name',
        'type', 'avatar_path', 'dateOfBirth'
    ];
    public function setPasswordAttribute($value)
    {
        return $this->attributes['password'] = Hash::make($value);
    }
    public function faculty_semester_coordinator()
    {
        return $this->hasMany(FacultySemesterCoordinator::class);
    }
}
