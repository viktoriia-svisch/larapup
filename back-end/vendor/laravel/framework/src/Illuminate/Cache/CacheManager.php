<?php
namespace Illuminate\Cache;
use Closure;
use InvalidArgumentException;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Contracts\Cache\Factory as FactoryContract;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
class CacheManager implements FactoryContract
{
    protected $app;
    protected $stores = [];
    protected $customCreators = [];
    public function __construct($app)
    {
        $this->app = $app;
    }
    public function store($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        return $this->stores[$name] = $this->get($name);
    }
    public function driver($driver = null)
    {
        return $this->store($driver);
    }
    protected function get($name)
    {
        return $this->stores[$name] ?? $this->resolve($name);
    }
    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        if (is_null($config)) {
            throw new InvalidArgumentException("Cache store [{$name}] is not defined.");
        }
        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        } else {
            $driverMethod = 'create'.ucfirst($config['driver']).'Driver';
            if (method_exists($this, $driverMethod)) {
                return $this->{$driverMethod}($config);
            } else {
                throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
            }
        }
    }
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }
    protected function createApcDriver(array $config)
    {
        $prefix = $this->getPrefix($config);
        return $this->repository(new ApcStore(new ApcWrapper, $prefix));
    }
    protected function createArrayDriver()
    {
        return $this->repository(new ArrayStore);
    }
    protected function createFileDriver(array $config)
    {
        return $this->repository(new FileStore($this->app['files'], $config['path']));
    }
    protected function createMemcachedDriver(array $config)
    {
        $prefix = $this->getPrefix($config);
        $memcached = $this->app['memcached.connector']->connect(
            $config['servers'],
            $config['persistent_id'] ?? null,
            $config['options'] ?? [],
            array_filter($config['sasl'] ?? [])
        );
        return $this->repository(new MemcachedStore($memcached, $prefix));
    }
    protected function createNullDriver()
    {
        return $this->repository(new NullStore);
    }
    protected function createRedisDriver(array $config)
    {
        $redis = $this->app['redis'];
        $connection = $config['connection'] ?? 'default';
        return $this->repository(new RedisStore($redis, $this->getPrefix($config), $connection));
    }
    protected function createDatabaseDriver(array $config)
    {
        $connection = $this->app['db']->connection($config['connection'] ?? null);
        return $this->repository(
            new DatabaseStore(
                $connection, $config['table'], $this->getPrefix($config)
            )
        );
    }
    public function repository(Store $store)
    {
        $repository = new Repository($store);
        if ($this->app->bound(DispatcherContract::class)) {
            $repository->setEventDispatcher(
                $this->app[DispatcherContract::class]
            );
        }
        return $repository;
    }
    protected function getPrefix(array $config)
    {
        return $config['prefix'] ?? $this->app['config']['cache.prefix'];
    }
    protected function getConfig($name)
    {
        return $this->app['config']["cache.stores.{$name}"];
    }
    public function getDefaultDriver()
    {
        return $this->app['config']['cache.default'];
    }
    public function setDefaultDriver($name)
    {
        $this->app['config']['cache.default'] = $name;
    }
    public function forgetDriver($name = null)
    {
        $name = $name ?? $this->getDefaultDriver();
        foreach ((array) $name as $cacheName) {
            if (isset($this->stores[$cacheName])) {
                unset($this->stores[$cacheName]);
            }
        }
        return $this;
    }
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);
        return $this;
    }
    public function __call($method, $parameters)
    {
        return $this->store()->$method(...$parameters);
    }
}
