<?php
namespace Illuminate\Auth;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
trait GuardHelpers
{
    protected $user;
    protected $provider;
    public function authenticate()
    {
        if (! is_null($user = $this->user())) {
            return $user;
        }
        throw new AuthenticationException;
    }
    public function hasUser()
    {
        return ! is_null($this->user);
    }
    public function check()
    {
        return ! is_null($this->user());
    }
    public function guest()
    {
        return ! $this->check();
    }
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
    }
    public function setUser(AuthenticatableContract $user)
    {
        $this->user = $user;
        return $this;
    }
    public function getProvider()
    {
        return $this->provider;
    }
    public function setProvider(UserProvider $provider)
    {
        $this->provider = $provider;
    }
}
