<?php
namespace Tymon\JWTAuth\Providers;
use Tymon\JWTAuth\Http\Parser\AuthHeaders;
use Tymon\JWTAuth\Http\Parser\InputSource;
use Tymon\JWTAuth\Http\Parser\QueryString;
use Tymon\JWTAuth\Http\Parser\LumenRouteParams;
class LumenServiceProvider extends AbstractServiceProvider
{
    public function boot()
    {
        $this->app->configure('jwt');
        $path = realpath(__DIR__.'/../../config/config.php');
        $this->mergeConfigFrom($path, 'jwt');
        $this->app->routeMiddleware($this->middlewareAliases);
        $this->extendAuthGuard();
        $this->app['tymon.jwt.parser']->setChain([
            new AuthHeaders,
            new QueryString,
            new InputSource,
            new LumenRouteParams,
        ]);
    }
}
