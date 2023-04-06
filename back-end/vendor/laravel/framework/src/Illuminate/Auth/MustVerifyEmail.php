<?php
namespace Illuminate\Auth;
trait MustVerifyEmail
{
    public function hasVerifiedEmail()
    {
        return ! is_null($this->email_verified_at);
    }
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }
    public function sendEmailVerificationNotification()
    {
        $this->notify(new Notifications\VerifyEmail);
    }
}
