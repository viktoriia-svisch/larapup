<?php
namespace Tymon\JWTAuth\Support;
trait CustomClaims
{
    protected $customClaims = [];
    public function customClaims(array $customClaims)
    {
        $this->customClaims = $customClaims;
        return $this;
    }
    public function claims(array $customClaims)
    {
        return $this->customClaims($customClaims);
    }
    public function getCustomClaims()
    {
        return $this->customClaims;
    }
}
