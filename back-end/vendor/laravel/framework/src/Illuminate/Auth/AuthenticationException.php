<?php
namespace Illuminate\Auth;
use Exception;
class AuthenticationException extends Exception
{
    protected $guards;
    protected $redirectTo;
    public function __construct($message = 'Unauthenticated.', array $guards = [], $redirectTo = null)
    {
        parent::__construct($message);
        $this->guards = $guards;
        $this->redirectTo = $redirectTo;
    }
    public function guards()
    {
        return $this->guards;
    }
    public function redirectTo()
    {
        return $this->redirectTo;
    }
}
