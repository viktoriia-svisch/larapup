<?php
namespace Illuminate\Foundation\Bus;
use Illuminate\Contracts\Bus\Dispatcher;
trait Dispatchable
{
    public static function dispatch()
    {
        return new PendingDispatch(new static(...func_get_args()));
    }
    public static function dispatchNow()
    {
        return app(Dispatcher::class)->dispatchNow(new static(...func_get_args()));
    }
    public static function withChain($chain)
    {
        return new PendingChain(static::class, $chain);
    }
}
