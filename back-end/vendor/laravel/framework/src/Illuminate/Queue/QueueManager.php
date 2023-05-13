<?php
namespace Illuminate\Queue;
use Closure;
use InvalidArgumentException;
use Illuminate\Contracts\Queue\Factory as FactoryContract;
use Illuminate\Contracts\Queue\Monitor as MonitorContract;
class QueueManager implements FactoryContract, MonitorContract
{
    protected $app;
    protected $connections = [];
    protected $connectors = [];
    public function __construct($app)
    {
        $this->app = $app;
    }
    public function before($callback)
    {
        $this->app['events']->listen(Events\JobProcessing::class, $callback);
    }
    public function after($callback)
    {
        $this->app['events']->listen(Events\JobProcessed::class, $callback);
    }
    public function exceptionOccurred($callback)
    {
        $this->app['events']->listen(Events\JobExceptionOccurred::class, $callback);
    }
    public function looping($callback)
    {
        $this->app['events']->listen(Events\Looping::class, $callback);
    }
    public function failing($callback)
    {
        $this->app['events']->listen(Events\JobFailed::class, $callback);
    }
    public function stopping($callback)
    {
        $this->app['events']->listen(Events\WorkerStopping::class, $callback);
    }
    public function connected($name = null)
    {
        return isset($this->connections[$name ?: $this->getDefaultDriver()]);
    }
    public function connection($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        if (! isset($this->connections[$name])) {
            $this->connections[$name] = $this->resolve($name);
            $this->connections[$name]->setContainer($this->app);
        }
        return $this->connections[$name];
    }
    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        return $this->getConnector($config['driver'])
                        ->connect($config)
                        ->setConnectionName($name);
    }
    protected function getConnector($driver)
    {
        if (! isset($this->connectors[$driver])) {
            throw new InvalidArgumentException("No connector for [$driver]");
        }
        return call_user_func($this->connectors[$driver]);
    }
    public function extend($driver, Closure $resolver)
    {
        return $this->addConnector($driver, $resolver);
    }
    public function addConnector($driver, Closure $resolver)
    {
        $this->connectors[$driver] = $resolver;
    }
    protected function getConfig($name)
    {
        if (! is_null($name) && $name !== 'null') {
            return $this->app['config']["queue.connections.{$name}"];
        }
        return ['driver' => 'null'];
    }
    public function getDefaultDriver()
    {
        return $this->app['config']['queue.default'];
    }
    public function setDefaultDriver($name)
    {
        $this->app['config']['queue.default'] = $name;
    }
    public function getName($connection = null)
    {
        return $connection ?: $this->getDefaultDriver();
    }
    public function isDownForMaintenance()
    {
        return $this->app->isDownForMaintenance();
    }
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}
