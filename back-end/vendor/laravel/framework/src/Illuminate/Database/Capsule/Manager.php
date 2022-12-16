<?php
namespace Illuminate\Database\Capsule;
use PDO;
use Illuminate\Container\Container;
use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Traits\CapsuleManagerTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Connectors\ConnectionFactory;
class Manager
{
    use CapsuleManagerTrait;
    protected $manager;
    public function __construct(Container $container = null)
    {
        $this->setupContainer($container ?: new Container);
        $this->setupDefaultConfiguration();
        $this->setupManager();
    }
    protected function setupDefaultConfiguration()
    {
        $this->container['config']['database.fetch'] = PDO::FETCH_OBJ;
        $this->container['config']['database.default'] = 'default';
    }
    protected function setupManager()
    {
        $factory = new ConnectionFactory($this->container);
        $this->manager = new DatabaseManager($this->container, $factory);
    }
    public static function connection($connection = null)
    {
        return static::$instance->getConnection($connection);
    }
    public static function table($table, $connection = null)
    {
        return static::$instance->connection($connection)->table($table);
    }
    public static function schema($connection = null)
    {
        return static::$instance->connection($connection)->getSchemaBuilder();
    }
    public function getConnection($name = null)
    {
        return $this->manager->connection($name);
    }
    public function addConnection(array $config, $name = 'default')
    {
        $connections = $this->container['config']['database.connections'];
        $connections[$name] = $config;
        $this->container['config']['database.connections'] = $connections;
    }
    public function bootEloquent()
    {
        Eloquent::setConnectionResolver($this->manager);
        if ($dispatcher = $this->getEventDispatcher()) {
            Eloquent::setEventDispatcher($dispatcher);
        }
    }
    public function setFetchMode($fetchMode)
    {
        $this->container['config']['database.fetch'] = $fetchMode;
        return $this;
    }
    public function getDatabaseManager()
    {
        return $this->manager;
    }
    public function getEventDispatcher()
    {
        if ($this->container->bound('events')) {
            return $this->container['events'];
        }
    }
    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->container->instance('events', $dispatcher);
    }
    public static function __callStatic($method, $parameters)
    {
        return static::connection()->$method(...$parameters);
    }
}
