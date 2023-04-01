<?php
namespace Illuminate\Queue\Capsule;
use Illuminate\Queue\QueueManager;
use Illuminate\Container\Container;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Support\Traits\CapsuleManagerTrait;
class Manager
{
    use CapsuleManagerTrait;
    protected $manager;
    public function __construct(Container $container = null)
    {
        $this->setupContainer($container ?: new Container);
        $this->setupDefaultConfiguration();
        $this->setupManager();
        $this->registerConnectors();
    }
    protected function setupDefaultConfiguration()
    {
        $this->container['config']['queue.default'] = 'default';
    }
    protected function setupManager()
    {
        $this->manager = new QueueManager($this->container);
    }
    protected function registerConnectors()
    {
        $provider = new QueueServiceProvider($this->container);
        $provider->registerConnectors($this->manager);
    }
    public static function connection($connection = null)
    {
        return static::$instance->getConnection($connection);
    }
    public static function push($job, $data = '', $queue = null, $connection = null)
    {
        return static::$instance->connection($connection)->push($job, $data, $queue);
    }
    public static function bulk($jobs, $data = '', $queue = null, $connection = null)
    {
        return static::$instance->connection($connection)->bulk($jobs, $data, $queue);
    }
    public static function later($delay, $job, $data = '', $queue = null, $connection = null)
    {
        return static::$instance->connection($connection)->later($delay, $job, $data, $queue);
    }
    public function getConnection($name = null)
    {
        return $this->manager->connection($name);
    }
    public function addConnection(array $config, $name = 'default')
    {
        $this->container['config']["queue.connections.{$name}"] = $config;
    }
    public function getQueueManager()
    {
        return $this->manager;
    }
    public function __call($method, $parameters)
    {
        return $this->manager->$method(...$parameters);
    }
    public static function __callStatic($method, $parameters)
    {
        return static::connection()->$method(...$parameters);
    }
}
