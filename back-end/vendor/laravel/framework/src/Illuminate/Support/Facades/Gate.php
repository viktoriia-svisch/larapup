<?php
namespace Illuminate\Support\Facades;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
class Gate extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GateContract::class;
    }
}
