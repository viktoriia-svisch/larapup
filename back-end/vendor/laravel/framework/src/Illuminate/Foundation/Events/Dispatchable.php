<?php
namespace Illuminate\Foundation\Events;
trait Dispatchable
{
    public static function dispatch()
    {
        return event(new static(...func_get_args()));
    }
    public static function broadcast()
    {
        return broadcast(new static(...func_get_args()));
    }
}
