<?php
namespace NunoMaduro\Collision\Adapters\Laravel;
use NunoMaduro\Collision\Provider;
use Illuminate\Support\ServiceProvider;
use NunoMaduro\Collision\Adapters\Phpunit\Listener;
use NunoMaduro\Collision\Contracts\Provider as ProviderContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use NunoMaduro\Collision\Contracts\Adapters\Phpunit\Listener as ListenerContract;
class CollisionServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register()
    {
        if ($this->app->runningInConsole() && ! $this->app->runningUnitTests()) {
            $this->app->singleton(ListenerContract::class, Listener::class);
            $this->app->bind(ProviderContract::class, Provider::class);
            $appExceptionHandler = $this->app->make(ExceptionHandlerContract::class);
            $this->app->singleton(
                ExceptionHandlerContract::class,
                function ($app) use ($appExceptionHandler) {
                    return new ExceptionHandler($app, $appExceptionHandler);
                }
            );
        }
    }
    public function provides()
    {
        return [ProviderContract::class];
    }
}
