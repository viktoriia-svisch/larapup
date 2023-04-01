<?php
namespace Illuminate\Broadcasting;
use Closure;
use Pusher\Pusher;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Illuminate\Broadcasting\Broadcasters\LogBroadcaster;
use Illuminate\Broadcasting\Broadcasters\NullBroadcaster;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\Broadcasters\RedisBroadcaster;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Contracts\Broadcasting\Factory as FactoryContract;
class BroadcastManager implements FactoryContract
{
    protected $app;
    protected $drivers = [];
    protected $customCreators = [];
    public function __construct($app)
    {
        $this->app = $app;
    }
    public function routes(array $attributes = null)
    {
        if ($this->app->routesAreCached()) {
            return;
        }
        $attributes = $attributes ?: ['middleware' => ['web']];
        $this->app['router']->group($attributes, function ($router) {
            $router->match(
                ['get', 'post'], '/broadcasting/auth',
                '\\'.BroadcastController::class.'@authenticate'
            );
        });
    }
    public function socket($request = null)
    {
        if (! $request && ! $this->app->bound('request')) {
            return;
        }
        $request = $request ?: $this->app['request'];
        return $request->header('X-Socket-ID');
    }
    public function event($event = null)
    {
        return new PendingBroadcast($this->app->make('events'), $event);
    }
    public function queue($event)
    {
        $connection = $event instanceof ShouldBroadcastNow ? 'sync' : null;
        if (is_null($connection) && isset($event->connection)) {
            $connection = $event->connection;
        }
        $queue = null;
        if (method_exists($event, 'broadcastQueue')) {
            $queue = $event->broadcastQueue();
        } elseif (isset($event->broadcastQueue)) {
            $queue = $event->broadcastQueue;
        } elseif (isset($event->queue)) {
            $queue = $event->queue;
        }
        $this->app->make('queue')->connection($connection)->pushOn(
            $queue, new BroadcastEvent(clone $event)
        );
    }
    public function connection($driver = null)
    {
        return $this->driver($driver);
    }
    public function driver($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        return $this->drivers[$name] = $this->get($name);
    }
    protected function get($name)
    {
        return $this->drivers[$name] ?? $this->resolve($name);
    }
    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }
        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';
        if (! method_exists($this, $driverMethod)) {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
        return $this->{$driverMethod}($config);
    }
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }
    protected function createPusherDriver(array $config)
    {
        $pusher = new Pusher(
            $config['key'], $config['secret'],
            $config['app_id'], $config['options'] ?? []
        );
        if ($config['log'] ?? false) {
            $pusher->setLogger($this->app->make(LoggerInterface::class));
        }
        return new PusherBroadcaster($pusher);
    }
    protected function createRedisDriver(array $config)
    {
        return new RedisBroadcaster(
            $this->app->make('redis'), $config['connection'] ?? null
        );
    }
    protected function createLogDriver(array $config)
    {
        return new LogBroadcaster(
            $this->app->make(LoggerInterface::class)
        );
    }
    protected function createNullDriver(array $config)
    {
        return new NullBroadcaster;
    }
    protected function getConfig($name)
    {
        if (! is_null($name) && $name !== 'null') {
            return $this->app['config']["broadcasting.connections.{$name}"];
        }
        return ['driver' => 'null'];
    }
    public function getDefaultDriver()
    {
        return $this->app['config']['broadcasting.default'];
    }
    public function setDefaultDriver($name)
    {
        $this->app['config']['broadcasting.default'] = $name;
    }
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
