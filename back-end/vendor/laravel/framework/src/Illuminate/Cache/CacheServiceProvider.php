<?php
namespace Illuminate\Cache;
use Illuminate\Support\ServiceProvider;
class CacheServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register()
    {
        $this->app->singleton('cache', function ($app) {
            return new CacheManager($app);
        });
        $this->app->singleton('cache.store', function ($app) {
            return $app['cache']->driver();
        });
        $this->app->singleton('memcached.connector', function () {
            return new MemcachedConnector;
        });
    }
    public function provides()
    {
        return [
            'cache', 'cache.store', 'memcached.connector',
        ];
    }
}
