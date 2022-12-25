<?php
namespace Tymon\JWTAuth\Validators;
use Tymon\JWTAuth\Support\RefreshFlow;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\Validator as ValidatorContract;
abstract class Validator implements ValidatorContract
{
    use RefreshFlow;
    public function isValid($value)
    {
        try {
            $this->check($value);
        } catch (JWTException $e) {
            return false;
        }
        return true;
    }
    abstract public function check($value);
}
