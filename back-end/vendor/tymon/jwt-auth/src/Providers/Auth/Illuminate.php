<?php
namespace Tymon\JWTAuth\Providers\Auth;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Illuminate\Contracts\Auth\Guard as GuardContract;
class Illuminate implements Auth
{
    protected $auth;
    public function __construct(GuardContract $auth)
    {
        $this->auth = $auth;
    }
    public function byCredentials(array $credentials)
    {
        return $this->auth->once($credentials);
    }
    public function byId($id)
    {
        return $this->auth->onceUsingId($id);
    }
    public function user()
    {
        return $this->auth->user();
    }
}
