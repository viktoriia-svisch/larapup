<?php
namespace Tymon\JWTAuth\Facades;
use Illuminate\Support\Facades\Facade;
class JWTFactory extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tymon.jwt.payload.factory';
    }
}
