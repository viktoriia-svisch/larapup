<?php
namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
class Guest extends Authenticate
{
    use SoftDeletes, Notifiable;
    protected $table = 'guests';
    protected $hidden = [
        'password', 'deleted_at'
    ];
    public function setPasswordAttribute($value)
    {
        return $this->attributes['password'] = Hash::make($value);
    }
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}
