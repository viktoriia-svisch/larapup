<?php
namespace Illuminate\Support\Testing\Fakes;
use Illuminate\Contracts\Bus\Dispatcher;
use PHPUnit\Framework\Assert as PHPUnit;
class BusFake implements Dispatcher
{
    protected $commands = [];
    public function assertDispatched($command, $callback = null)
    {
        if (is_numeric($callback)) {
            return $this->assertDispatchedTimes($command, $callback);
        }
        PHPUnit::assertTrue(
            $this->dispatched($command, $callback)->count() > 0,
            "The expected [{$command}] job was not dispatched."
        );
    }
    protected function assertDispatchedTimes($command, $times = 1)
    {
        PHPUnit::assertTrue(
            ($count = $this->dispatched($command)->count()) === $times,
            "The expected [{$command}] job was pushed {$count} times instead of {$times} times."
        );
    }
    public function assertNotDispatched($command, $callback = null)
    {
        PHPUnit::assertTrue(
            $this->dispatched($command, $callback)->count() === 0,
            "The unexpected [{$command}] job was dispatched."
        );
    }
    public function dispatched($command, $callback = null)
    {
        if (! $this->hasDispatched($command)) {
            return collect();
        }
        $callback = $callback ?: function () {
            return true;
        };
        return collect($this->commands[$command])->filter(function ($command) use ($callback) {
            return $callback($command);
        });
    }
    public function hasDispatched($command)
    {
        return isset($this->commands[$command]) && ! empty($this->commands[$command]);
    }
    public function dispatch($command)
    {
        return $this->dispatchNow($command);
    }
    public function dispatchNow($command, $handler = null)
    {
        $this->commands[get_class($command)][] = $command;
    }
    public function pipeThrough(array $pipes)
    {
    }
    public function hasCommandHandler($command)
    {
        return false;
    }
    public function getCommandHandler($command)
    {
        return false;
    }
    public function map(array $map)
    {
        return $this;
    }
}
