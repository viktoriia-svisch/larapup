<?php
namespace Illuminate\Support\Facades;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastingFactoryContract;
class Broadcast extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BroadcastingFactoryContract::class;
    }
}
