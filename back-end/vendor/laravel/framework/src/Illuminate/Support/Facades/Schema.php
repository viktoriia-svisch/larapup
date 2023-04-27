<?php
namespace Illuminate\Support\Facades;
class Schema extends Facade
{
    public static function connection($name)
    {
        return static::$app['db']->connection($name)->getSchemaBuilder();
    }
    protected static function getFacadeAccessor()
    {
        return static::$app['db']->connection()->getSchemaBuilder();
    }
}
