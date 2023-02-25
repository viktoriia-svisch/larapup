<?php
namespace Illuminate\Auth\Events;
class Attempting
{
    public $guard;
    public $credentials;
    public $remember;
    public function __construct($guard, $credentials, $remember)
    {
        $this->guard = $guard;
        $this->remember = $remember;
        $this->credentials = $credentials;
    }
}
