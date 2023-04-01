<?php
namespace Illuminate\Auth;
use Closure;
use InvalidArgumentException;
use Illuminate\Contracts\Auth\Factory as FactoryContract;
class AuthManager implements FactoryContract
{
    use CreatesUserProviders;
    protected $app;
    protected $customCreators = [];
    protected $guards = [];
    protected $userResolver;
    public function __construct($app)
    {
        $this->app = $app;
        $this->userResolver = function ($guard = null) {
            return $this->guard($guard)->user();
        };
    }
    public function guard($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }
    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        if (is_null($config)) {
            throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
        }
        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($name, $config);
        }
        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';
        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($name, $config);
        }
        throw new InvalidArgumentException("Auth driver [{$config['driver']}] for guard [{$name}] is not defined.");
    }
    protected function callCustomCreator($name, array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $name, $config);
    }
    public function createSessionDriver($name, $config)
    {
        $provider = $this->createUserProvider($config['provider'] ?? null);
        $guard = new SessionGuard($name, $provider, $this->app['session.store']);
        if (method_exists($guard, 'setCookieJar')) {
            $guard->setCookieJar($this->app['cookie']);
        }
        if (method_exists($guard, 'setDispatcher')) {
            $guard->setDispatcher($this->app['events']);
        }
        if (method_exists($guard, 'setRequest')) {
            $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
        }
        return $guard;
    }
    public function createTokenDriver($name, $config)
    {
        $guard = new TokenGuard(
            $this->createUserProvider($config['provider'] ?? null),
            $this->app['request'],
            $config['input_key'] ?? 'api_token',
            $config['storage_key'] ?? 'api_token'
        );
        $this->app->refresh('request', $guard, 'setRequest');
        return $guard;
    }
    protected function getConfig($name)
    {
        return $this->app['config']["auth.guards.{$name}"];
    }
    public function getDefaultDriver()
    {
        return $this->app['config']['auth.defaults.guard'];
    }
    public function shouldUse($name)
    {
        $name = $name ?: $this->getDefaultDriver();
        $this->setDefaultDriver($name);
        $this->userResolver = function ($name = null) {
            return $this->guard($name)->user();
        };
    }
    public function setDefaultDriver($name)
    {
        $this->app['config']['auth.defaults.guard'] = $name;
    }
    public function viaRequest($driver, callable $callback)
    {
        return $this->extend($driver, function () use ($callback) {
            $guard = new RequestGuard($callback, $this->app['request'], $this->createUserProvider());
            $this->app->refresh('request', $guard, 'setRequest');
            return $guard;
        });
    }
    public function userResolver()
    {
        return $this->userResolver;
    }
    public function resolveUsersUsing(Closure $userResolver)
    {
        $this->userResolver = $userResolver;
        return $this;
    }
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }
    public function provider($name, Closure $callback)
    {
        $this->customProviderCreators[$name] = $callback;
        return $this;
    }
    public function __call($method, $parameters)
    {
        return $this->guard()->{$method}(...$parameters);
    }
}
