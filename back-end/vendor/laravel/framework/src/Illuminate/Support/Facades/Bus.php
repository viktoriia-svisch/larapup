<?php
namespace Illuminate\Support\Facades;
use Illuminate\Support\Testing\Fakes\BusFake;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcherContract;
class Bus extends Facade
{
    public static function fake()
    {
        static::swap(new BusFake);
    }
    protected static function getFacadeAccessor()
    {
        return BusDispatcherContract::class;
    }
}
