<?php
namespace Illuminate\Redis;
use InvalidArgumentException;
use Illuminate\Contracts\Redis\Factory;
use Illuminate\Redis\Connections\Connection;
class RedisManager implements Factory
{
    protected $app;
    protected $driver;
    protected $config;
    protected $connections;
    protected $events = false;
    public function __construct($app, $driver, array $config)
    {
        $this->app = $app;
        $this->driver = $driver;
        $this->config = $config;
    }
    public function connection($name = null)
    {
        $name = $name ?: 'default';
        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }
        return $this->connections[$name] = $this->configure(
            $this->resolve($name), $name
        );
    }
    public function resolve($name = null)
    {
        $name = $name ?: 'default';
        $options = $this->config['options'] ?? [];
        if (isset($this->config[$name])) {
            return $this->connector()->connect($this->config[$name], $options);
        }
        if (isset($this->config['clusters'][$name])) {
            return $this->resolveCluster($name);
        }
        throw new InvalidArgumentException("Redis connection [{$name}] not configured.");
    }
    protected function resolveCluster($name)
    {
        $clusterOptions = $this->config['clusters']['options'] ?? [];
        return $this->connector()->connectToCluster(
            $this->config['clusters'][$name], $clusterOptions, $this->config['options'] ?? []
        );
    }
    protected function configure(Connection $connection, $name)
    {
        $connection->setName($name);
        if ($this->events && $this->app->bound('events')) {
            $connection->setEventDispatcher($this->app->make('events'));
        }
        return $connection;
    }
    protected function connector()
    {
        switch ($this->driver) {
            case 'predis':
                return new Connectors\PredisConnector;
            case 'phpredis':
                return new Connectors\PhpRedisConnector;
        }
    }
    public function connections()
    {
        return $this->connections;
    }
    public function enableEvents()
    {
        $this->events = true;
    }
    public function disableEvents()
    {
        $this->events = false;
    }
    public function __call($method, $parameters)
    {
        return $this->connection()->{$method}(...$parameters);
    }
}
