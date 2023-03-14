<?php
namespace Tymon\JWTAuth\Providers;
class LaravelServiceProvider extends AbstractServiceProvider
{
    public function boot()
    {
        $path = realpath(__DIR__.'/../../config/config.php');
        $this->publishes([$path => config_path('jwt.php')], 'config');
        $this->mergeConfigFrom($path, 'jwt');
        $this->aliasMiddleware();
        $this->extendAuthGuard();
    }
    protected function aliasMiddleware()
    {
        $router = $this->app['router'];
        $method = method_exists($router, 'aliasMiddleware') ? 'aliasMiddleware' : 'middleware';
        foreach ($this->middlewareAliases as $alias => $middleware) {
            $router->$method($alias, $middleware);
        }
    }
}
