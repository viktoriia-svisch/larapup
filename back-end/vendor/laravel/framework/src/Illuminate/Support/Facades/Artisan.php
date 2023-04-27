<?php
namespace Illuminate\Support\Facades;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
class Artisan extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ConsoleKernelContract::class;
    }
}
