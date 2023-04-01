<?php
namespace Tymon\JWTAuth\Contracts;
interface JWTSubject
{
    public function getJWTIdentifier();
    public function getJWTCustomClaims();
}
