<?php
namespace Illuminate\Auth\Events;
use Illuminate\Queue\SerializesModels;
class Login
{
    use SerializesModels;
    public $guard;
    public $user;
    public $remember;
    public function __construct($guard, $user, $remember)
    {
        $this->user = $user;
        $this->guard = $guard;
        $this->remember = $remember;
    }
}
