<?php
namespace Illuminate\Database;
use PDO;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Database\Connectors\ConnectionFactory;
class DatabaseManager implements ConnectionResolverInterface
{
    protected $app;
    protected $factory;
    protected $connections = [];
    protected $extensions = [];
    public function __construct($app, ConnectionFactory $factory)
    {
        $this->app = $app;
        $this->factory = $factory;
    }
    public function connection($name = null)
    {
        [$database, $type] = $this->parseConnectionName($name);
        $name = $name ?: $database;
        if (! isset($this->connections[$name])) {
            $this->connections[$name] = $this->configure(
                $this->makeConnection($database), $type
            );
        }
        return $this->connections[$name];
    }
    protected function parseConnectionName($name)
    {
        $name = $name ?: $this->getDefaultConnection();
        return Str::endsWith($name, ['::read', '::write'])
                            ? explode('::', $name, 2) : [$name, null];
    }
    protected function makeConnection($name)
    {
        $config = $this->configuration($name);
        if (isset($this->extensions[$name])) {
            return call_user_func($this->extensions[$name], $config, $name);
        }
        if (isset($this->extensions[$driver = $config['driver']])) {
            return call_user_func($this->extensions[$driver], $config, $name);
        }
        return $this->factory->make($config, $name);
    }
    protected function configuration($name)
    {
        $name = $name ?: $this->getDefaultConnection();
        $connections = $this->app['config']['database.connections'];
        if (is_null($config = Arr::get($connections, $name))) {
            throw new InvalidArgumentException("Database [{$name}] not configured.");
        }
        return $config;
    }
    protected function configure(Connection $connection, $type)
    {
        $connection = $this->setPdoForType($connection, $type);
        if ($this->app->bound('events')) {
            $connection->setEventDispatcher($this->app['events']);
        }
        $connection->setReconnector(function ($connection) {
            $this->reconnect($connection->getName());
        });
        return $connection;
    }
    protected function setPdoForType(Connection $connection, $type = null)
    {
        if ($type === 'read') {
            $connection->setPdo($connection->getReadPdo());
        } elseif ($type === 'write') {
            $connection->setReadPdo($connection->getPdo());
        }
        return $connection;
    }
    public function purge($name = null)
    {
        $name = $name ?: $this->getDefaultConnection();
        $this->disconnect($name);
        unset($this->connections[$name]);
    }
    public function disconnect($name = null)
    {
        if (isset($this->connections[$name = $name ?: $this->getDefaultConnection()])) {
            $this->connections[$name]->disconnect();
        }
    }
    public function reconnect($name = null)
    {
        $this->disconnect($name = $name ?: $this->getDefaultConnection());
        if (! isset($this->connections[$name])) {
            return $this->connection($name);
        }
        return $this->refreshPdoConnections($name);
    }
    protected function refreshPdoConnections($name)
    {
        $fresh = $this->makeConnection($name);
        return $this->connections[$name]
                                ->setPdo($fresh->getPdo())
                                ->setReadPdo($fresh->getReadPdo());
    }
    public function getDefaultConnection()
    {
        return $this->app['config']['database.default'];
    }
    public function setDefaultConnection($name)
    {
        $this->app['config']['database.default'] = $name;
    }
    public function supportedDrivers()
    {
        return ['mysql', 'pgsql', 'sqlite', 'sqlsrv'];
    }
    public function availableDrivers()
    {
        return array_intersect(
            $this->supportedDrivers(),
            str_replace('dblib', 'sqlsrv', PDO::getAvailableDrivers())
        );
    }
    public function extend($name, callable $resolver)
    {
        $this->extensions[$name] = $resolver;
    }
    public function getConnections()
    {
        return $this->connections;
    }
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}
