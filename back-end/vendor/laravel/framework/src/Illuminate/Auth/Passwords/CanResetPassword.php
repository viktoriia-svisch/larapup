<?php
namespace Illuminate\Auth\Passwords;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
trait CanResetPassword
{
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
