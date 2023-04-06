<?php
namespace App\Models;
use DateTime;
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
    public function setDateOfBirthAttribute($value)
    {
        $date = DateTime::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        $this->attributes['dateOfBirth'] = $date;
    }
    public function setPasswordAttribute($value)
    {
        return $this->attributes['password'] = Hash::make($value);
    }
    public function faculty_semester_coordinator()
    {
        return $this->hasMany(FacultySemesterCoordinator::class);
    }
    public function comment_coordinator(){
        return $this->hasMany(CommentCoordinator::class);
    }
}
