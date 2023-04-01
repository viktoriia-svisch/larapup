<?php
namespace Illuminate\Support\Facades;
use Illuminate\Support\Testing\Fakes\QueueFake;
class Queue extends Facade
{
    public static function fake()
    {
        static::swap(new QueueFake(static::getFacadeApplication()));
    }
    protected static function getFacadeAccessor()
    {
        return 'queue';
    }
}
