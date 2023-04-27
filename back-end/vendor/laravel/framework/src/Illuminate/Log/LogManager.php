<?php
namespace Illuminate\Log;
use Closure;
use Throwable;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\WhatFailureGroupHandler;
class LogManager implements LoggerInterface
{
    use ParsesLogConfiguration;
    protected $app;
    protected $channels = [];
    protected $customCreators = [];
    public function __construct($app)
    {
        $this->app = $app;
    }
    public function stack(array $channels, $channel = null)
    {
        return new Logger(
            $this->createStackDriver(compact('channels', 'channel')),
            $this->app['events']
        );
    }
    public function channel($channel = null)
    {
        return $this->driver($channel);
    }
    public function driver($driver = null)
    {
        return $this->get($driver ?? $this->getDefaultDriver());
    }
    protected function get($name)
    {
        try {
            return $this->channels[$name] ?? with($this->resolve($name), function ($logger) use ($name) {
                return $this->channels[$name] = $this->tap($name, new Logger($logger, $this->app['events']));
            });
        } catch (Throwable $e) {
            return tap($this->createEmergencyLogger(), function ($logger) use ($e) {
                $logger->emergency('Unable to create configured logger. Using emergency logger.', [
                    'exception' => $e,
                ]);
            });
        }
    }
    protected function tap($name, Logger $logger)
    {
        foreach ($this->configurationFor($name)['tap'] ?? [] as $tap) {
            [$class, $arguments] = $this->parseTap($tap);
            $this->app->make($class)->__invoke($logger, ...explode(',', $arguments));
        }
        return $logger;
    }
    protected function parseTap($tap)
    {
        return Str::contains($tap, ':') ? explode(':', $tap, 2) : [$tap, ''];
    }
    protected function createEmergencyLogger()
    {
        return new Logger(new Monolog('laravel', $this->prepareHandlers([new StreamHandler(
                $this->app->storagePath().'/logs/laravel.log', $this->level(['level' => 'debug'])
        )])), $this->app['events']);
    }
    protected function resolve($name)
    {
        $config = $this->configurationFor($name);
        if (is_null($config)) {
            throw new InvalidArgumentException("Log [{$name}] is not defined.");
        }
        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }
        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';
        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }
        throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
    }
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }
    protected function createCustomDriver(array $config)
    {
        $factory = is_callable($via = $config['via']) ? $via : $this->app->make($via);
        return $factory($config);
    }
    protected function createStackDriver(array $config)
    {
        $handlers = collect($config['channels'])->flatMap(function ($channel) {
            return $this->channel($channel)->getHandlers();
        })->all();
        if ($config['ignore_exceptions'] ?? false) {
            $handlers = [new WhatFailureGroupHandler($handlers)];
        }
        return new Monolog($this->parseChannel($config), $handlers);
    }
    protected function createSingleDriver(array $config)
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(
                new StreamHandler(
                    $config['path'], $this->level($config),
                    $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
                ), $config
            ),
        ]);
    }
    protected function createDailyDriver(array $config)
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new RotatingFileHandler(
                $config['path'], $config['days'] ?? 7, $this->level($config),
                $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
            ), $config),
        ]);
    }
    protected function createSlackDriver(array $config)
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new SlackWebhookHandler(
                $config['url'],
                $config['channel'] ?? null,
                $config['username'] ?? 'Laravel',
                $config['attachment'] ?? true,
                $config['emoji'] ?? ':boom:',
                $config['short'] ?? false,
                $config['context'] ?? true,
                $this->level($config),
                $config['bubble'] ?? true,
                $config['exclude_fields'] ?? []
            ), $config),
        ]);
    }
    protected function createSyslogDriver(array $config)
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new SyslogHandler(
                $this->app['config']['app.name'], $config['facility'] ?? LOG_USER, $this->level($config)
            ), $config),
        ]);
    }
    protected function createErrorlogDriver(array $config)
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new ErrorLogHandler(
                $config['type'] ?? ErrorLogHandler::OPERATING_SYSTEM, $this->level($config)
            )),
        ]);
    }
    protected function createMonologDriver(array $config)
    {
        if (! is_a($config['handler'], HandlerInterface::class, true)) {
            throw new InvalidArgumentException(
                $config['handler'].' must be an instance of '.HandlerInterface::class
            );
        }
        $with = array_merge(
            $config['with'] ?? [],
            $config['handler_with'] ?? []
        );
        return new Monolog($this->parseChannel($config), [$this->prepareHandler(
            $this->app->make($config['handler'], $with), $config
        )]);
    }
    protected function prepareHandlers(array $handlers)
    {
        foreach ($handlers as $key => $handler) {
            $handlers[$key] = $this->prepareHandler($handler);
        }
        return $handlers;
    }
    protected function prepareHandler(HandlerInterface $handler, array $config = [])
    {
        if (! isset($config['formatter'])) {
            $handler->setFormatter($this->formatter());
        } elseif ($config['formatter'] !== 'default') {
            $handler->setFormatter($this->app->make($config['formatter'], $config['formatter_with'] ?? []));
        }
        return $handler;
    }
    protected function formatter()
    {
        return tap(new LineFormatter(null, null, true, true), function ($formatter) {
            $formatter->includeStacktraces();
        });
    }
    protected function getFallbackChannelName()
    {
        return $this->app->bound('env') ? $this->app->environment() : 'production';
    }
    protected function configurationFor($name)
    {
        return $this->app['config']["logging.channels.{$name}"];
    }
    public function getDefaultDriver()
    {
        return $this->app['config']['logging.default'];
    }
    public function setDefaultDriver($name)
    {
        $this->app['config']['logging.default'] = $name;
    }
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);
        return $this;
    }
    public function emergency($message, array $context = [])
    {
        $this->driver()->emergency($message, $context);
    }
    public function alert($message, array $context = [])
    {
        $this->driver()->alert($message, $context);
    }
    public function critical($message, array $context = [])
    {
        $this->driver()->critical($message, $context);
    }
    public function error($message, array $context = [])
    {
        $this->driver()->error($message, $context);
    }
    public function warning($message, array $context = [])
    {
        $this->driver()->warning($message, $context);
    }
    public function notice($message, array $context = [])
    {
        $this->driver()->notice($message, $context);
    }
    public function info($message, array $context = [])
    {
        $this->driver()->info($message, $context);
    }
    public function debug($message, array $context = [])
    {
        $this->driver()->debug($message, $context);
    }
    public function log($level, $message, array $context = [])
    {
        $this->driver()->log($level, $message, $context);
    }
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
