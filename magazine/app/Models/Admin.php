<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
class Admin extends Authenticate
{
    use Notifiable;
    protected $table = 'admins';
    protected $hidden = [
        'password', 'deleted_at'
    ];
    public function setPasswordAttribute($value)
    {
        return $this->attributes['password'] = Hash::make($value);
    }
}
