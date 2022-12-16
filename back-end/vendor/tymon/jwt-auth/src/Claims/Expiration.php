<?php
namespace Tymon\JWTAuth\Claims;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
class Expiration extends Claim
{
    use DatetimeTrait;
    protected $name = 'exp';
    public function validatePayload()
    {
        if ($this->isPast($this->getValue())) {
            throw new TokenExpiredException('Token has expired');
        }
    }
}
