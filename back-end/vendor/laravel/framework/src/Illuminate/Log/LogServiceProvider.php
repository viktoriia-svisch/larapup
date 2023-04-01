<?php
namespace Illuminate\Log;
use Illuminate\Support\ServiceProvider;
class LogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('log', function () {
            return new LogManager($this->app);
        });
    }
}
