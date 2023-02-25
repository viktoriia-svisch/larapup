<?php
namespace Illuminate\Bus;
use Closure;
use RuntimeException;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Bus\QueueingDispatcher;
class Dispatcher implements QueueingDispatcher
{
    protected $container;
    protected $pipeline;
    protected $pipes = [];
    protected $handlers = [];
    protected $queueResolver;
    public function __construct(Container $container, Closure $queueResolver = null)
    {
        $this->container = $container;
        $this->queueResolver = $queueResolver;
        $this->pipeline = new Pipeline($container);
    }
    public function dispatch($command)
    {
        if ($this->queueResolver && $this->commandShouldBeQueued($command)) {
            return $this->dispatchToQueue($command);
        }
        return $this->dispatchNow($command);
    }
    public function dispatchNow($command, $handler = null)
    {
        if ($handler || $handler = $this->getCommandHandler($command)) {
            $callback = function ($command) use ($handler) {
                return $handler->handle($command);
            };
        } else {
            $callback = function ($command) {
                return $this->container->call([$command, 'handle']);
            };
        }
        return $this->pipeline->send($command)->through($this->pipes)->then($callback);
    }
    public function hasCommandHandler($command)
    {
        return array_key_exists(get_class($command), $this->handlers);
    }
    public function getCommandHandler($command)
    {
        if ($this->hasCommandHandler($command)) {
            return $this->container->make($this->handlers[get_class($command)]);
        }
        return false;
    }
    protected function commandShouldBeQueued($command)
    {
        return $command instanceof ShouldQueue;
    }
    public function dispatchToQueue($command)
    {
        $connection = $command->connection ?? null;
        $queue = call_user_func($this->queueResolver, $connection);
        if (! $queue instanceof Queue) {
            throw new RuntimeException('Queue resolver did not return a Queue implementation.');
        }
        if (method_exists($command, 'queue')) {
            return $command->queue($queue, $command);
        }
        return $this->pushCommandToQueue($queue, $command);
    }
    protected function pushCommandToQueue($queue, $command)
    {
        if (isset($command->queue, $command->delay)) {
            return $queue->laterOn($command->queue, $command->delay, $command);
        }
        if (isset($command->queue)) {
            return $queue->pushOn($command->queue, $command);
        }
        if (isset($command->delay)) {
            return $queue->later($command->delay, $command);
        }
        return $queue->push($command);
    }
    public function pipeThrough(array $pipes)
    {
        $this->pipes = $pipes;
        return $this;
    }
    public function map(array $map)
    {
        $this->handlers = array_merge($this->handlers, $map);
        return $this;
    }
}
