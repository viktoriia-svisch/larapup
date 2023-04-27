<?php
namespace Illuminate\Support\Facades;
class Blade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return static::$app['view']->getEngineResolver()->resolve('blade')->getCompiler();
    }
}
