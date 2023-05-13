<?php
namespace Tymon\JWTAuth\Http\Parser;
trait KeyTrait
{
    protected $key = 'token';
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }
    public function getKey()
    {
        return $this->key;
    }
}
