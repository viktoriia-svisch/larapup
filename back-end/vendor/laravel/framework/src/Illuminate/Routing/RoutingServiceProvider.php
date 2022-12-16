<?php
namespace Illuminate\Routing;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response as PsrResponse;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;
use Illuminate\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;
class RoutingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerRouter();
        $this->registerUrlGenerator();
        $this->registerRedirector();
        $this->registerPsrRequest();
        $this->registerPsrResponse();
        $this->registerResponseFactory();
        $this->registerControllerDispatcher();
    }
    protected function registerRouter()
    {
        $this->app->singleton('router', function ($app) {
            return new Router($app['events'], $app);
        });
    }
    protected function registerUrlGenerator()
    {
        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();
            $app->instance('routes', $routes);
            $url = new UrlGenerator(
                $routes, $app->rebinding(
                    'request', $this->requestRebinder()
                ), $app['config']['app.asset_url']
            );
            $url->setSessionResolver(function () {
                return $this->app['session'];
            });
            $url->setKeyResolver(function () {
                return $this->app->make('config')->get('app.key');
            });
            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });
            return $url;
        });
    }
    protected function requestRebinder()
    {
        return function ($app, $request) {
            $app['url']->setRequest($request);
        };
    }
    protected function registerRedirector()
    {
        $this->app->singleton('redirect', function ($app) {
            $redirector = new Redirector($app['url']);
            if (isset($app['session.store'])) {
                $redirector->setSession($app['session.store']);
            }
            return $redirector;
        });
    }
    protected function registerPsrRequest()
    {
        $this->app->bind(ServerRequestInterface::class, function ($app) {
            return (new DiactorosFactory)->createRequest($app->make('request'));
        });
    }
    protected function registerPsrResponse()
    {
        $this->app->bind(ResponseInterface::class, function () {
            return new PsrResponse;
        });
    }
    protected function registerResponseFactory()
    {
        $this->app->singleton(ResponseFactoryContract::class, function ($app) {
            return new ResponseFactory($app[ViewFactoryContract::class], $app['redirect']);
        });
    }
    protected function registerControllerDispatcher()
    {
        $this->app->singleton(ControllerDispatcherContract::class, function ($app) {
            return new ControllerDispatcher($app);
        });
    }
}
