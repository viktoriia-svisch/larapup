<?php
namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
class Student extends Authenticate
{
    use SoftDeletes, Notifiable;
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name', 'email', 'password',
    ];
    protected $hidden = [
        'password', 'deleted_at'
    ];
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
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
