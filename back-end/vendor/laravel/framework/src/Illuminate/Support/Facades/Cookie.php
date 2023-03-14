<?php
namespace Illuminate\Support\Facades;
class Cookie extends Facade
{
    public static function has($key)
    {
        return ! is_null(static::$app['request']->cookie($key, null));
    }
    public static function get($key = null, $default = null)
    {
        return static::$app['request']->cookie($key, $default);
    }
    protected static function getFacadeAccessor()
    {
        return 'cookie';
    }
}
