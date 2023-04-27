<?php
namespace Illuminate\Support\Facades;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;
class Response extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ResponseFactoryContract::class;
    }
}
