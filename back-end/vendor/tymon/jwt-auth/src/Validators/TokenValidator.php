<?php
namespace Tymon\JWTAuth\Validators;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
class TokenValidator extends Validator
{
    public function check($value)
    {
        return $this->validateStructure($value);
    }
    protected function validateStructure($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new TokenInvalidException('Wrong number of segments');
        }
        $parts = array_filter(array_map('trim', $parts));
        if (count($parts) !== 3 || implode('.', $parts) !== $token) {
            throw new TokenInvalidException('Malformed token');
        }
        return $token;
    }
}
