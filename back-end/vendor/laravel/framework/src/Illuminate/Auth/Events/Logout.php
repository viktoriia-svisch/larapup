<?php
namespace Illuminate\Auth\Events;
use Illuminate\Queue\SerializesModels;
class Logout
{
    use SerializesModels;
    public $guard;
    public $user;
    public function __construct($guard, $user)
    {
        $this->user = $user;
        $this->guard = $guard;
    }
}
