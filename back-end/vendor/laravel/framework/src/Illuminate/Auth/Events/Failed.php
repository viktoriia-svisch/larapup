<?php
namespace Illuminate\Auth\Events;
class Failed
{
    public $guard;
    public $user;
    public $credentials;
    public function __construct($guard, $user, $credentials)
    {
        $this->user = $user;
        $this->guard = $guard;
        $this->credentials = $credentials;
    }
}
