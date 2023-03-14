<?php
namespace Tymon\JWTAuth\Validators;
use Tymon\JWTAuth\Claims\Collection;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
class PayloadValidator extends Validator
{
    protected $requiredClaims = [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ];
    protected $refreshTTL = 20160;
    public function check($value)
    {
        $this->validateStructure($value);
        return $this->refreshFlow ? $this->validateRefresh($value) : $this->validatePayload($value);
    }
    protected function validateStructure(Collection $claims)
    {
        if ($this->requiredClaims && ! $claims->hasAllClaims($this->requiredClaims)) {
            throw new TokenInvalidException('JWT payload does not contain the required claims');
        }
    }
    protected function validatePayload(Collection $claims)
    {
        return $claims->validate('payload');
    }
    protected function validateRefresh(Collection $claims)
    {
        return $this->refreshTTL === null ? $claims : $claims->validate('refresh', $this->refreshTTL);
    }
    public function setRequiredClaims(array $claims)
    {
        $this->requiredClaims = $claims;
        return $this;
    }
    public function setRefreshTTL($ttl)
    {
        $this->refreshTTL = $ttl;
        return $this;
    }
}
