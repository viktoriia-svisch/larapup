<?php
namespace Illuminate\Hashing;
use Illuminate\Support\ServiceProvider;
class HashServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register()
    {
        $this->app->singleton('hash', function ($app) {
            return new HashManager($app);
        });
        $this->app->singleton('hash.driver', function ($app) {
            return $app['hash']->driver();
        });
    }
    public function provides()
    {
        return ['hash', 'hash.driver'];
    }
}
