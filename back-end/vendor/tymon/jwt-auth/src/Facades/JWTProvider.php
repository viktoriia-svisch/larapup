<?php
namespace Tymon\JWTAuth\Facades;
use Illuminate\Support\Facades\Facade;
class JWTProvider extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tymon.jwt.provider.jwt';
    }
}
