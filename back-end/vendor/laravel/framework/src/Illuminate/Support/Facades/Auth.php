<?php
namespace Illuminate\Support\Facades;
class Auth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'auth';
    }
    public static function routes(array $options = [])
    {
        static::$app->make('router')->auth($options);
    }
}
