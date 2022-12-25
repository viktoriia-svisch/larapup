<?php
namespace Tymon\JWTAuth\Claims;
use Tymon\JWTAuth\Exceptions\InvalidClaimException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
class NotBefore extends Claim
{
    use DatetimeTrait {
        validateCreate as commonValidateCreate;
    }
    protected $name = 'nbf';
    public function validateCreate($value)
    {
        $this->commonValidateCreate($value);
        if ($this->isFuture($value)) {
            throw new InvalidClaimException($this);
        }
        return $value;
    }
    public function validatePayload()
    {
        if ($this->isFuture($this->getValue())) {
            throw new TokenInvalidException('Not Before (nbf) timestamp cannot be in the future');
        }
    }
}
