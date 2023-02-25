<?php
namespace Illuminate\Auth;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
class GenericUser implements UserContract
{
    protected $attributes;
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }
    public function getAuthIdentifierName()
    {
        return 'id';
    }
    public function getAuthIdentifier()
    {
        $name = $this->getAuthIdentifierName();
        return $this->attributes[$name];
    }
    public function getAuthPassword()
    {
        return $this->attributes['password'];
    }
    public function getRememberToken()
    {
        return $this->attributes[$this->getRememberTokenName()];
    }
    public function setRememberToken($value)
    {
        $this->attributes[$this->getRememberTokenName()] = $value;
    }
    public function getRememberTokenName()
    {
        return 'remember_token';
    }
    public function __get($key)
    {
        return $this->attributes[$key];
    }
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }
}
