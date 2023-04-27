<?php
namespace Illuminate\Support\Facades;
class Input extends Facade
{
    public static function get($key = null, $default = null)
    {
        return static::$app['request']->input($key, $default);
    }
    protected static function getFacadeAccessor()
    {
        return 'request';
    }
}
