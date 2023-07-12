<?php
namespace Tymon\JWTAuth\Providers\Auth;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Cartalyst\Sentinel\Sentinel as SentinelAuth;
class Sentinel implements Auth
{
    protected $sentinel;
    public function __construct(SentinelAuth $sentinel)
    {
        $this->sentinel = $sentinel;
    }
    public function byCredentials(array $credentials)
    {
        return $this->sentinel->stateless($credentials);
    }
    public function byId($id)
    {
        if ($user = $this->sentinel->getUserRepository()->findById($id)) {
            $this->sentinel->setUser($user);
            return true;
        }
        return false;
    }
    public function user()
    {
        return $this->sentinel->getUser();
    }
}
