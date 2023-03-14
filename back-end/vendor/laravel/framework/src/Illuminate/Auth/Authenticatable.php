<?php
namespace Illuminate\Auth;
trait Authenticatable
{
    protected $rememberTokenName = 'remember_token';
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }
    public function getAuthPassword()
    {
        return $this->password;
    }
    public function getRememberToken()
    {
        if (! empty($this->getRememberTokenName())) {
            return (string) $this->{$this->getRememberTokenName()};
        }
    }
    public function setRememberToken($value)
    {
        if (! empty($this->getRememberTokenName())) {
            $this->{$this->getRememberTokenName()} = $value;
        }
    }
    public function getRememberTokenName()
    {
        return $this->rememberTokenName;
    }
}
