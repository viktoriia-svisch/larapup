<?php
namespace Illuminate\Support\Testing\Fakes;
use Closure;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert as PHPUnit;
use Illuminate\Contracts\Events\Dispatcher;
class EventFake implements Dispatcher
{
    protected $dispatcher;
    protected $eventsToFake;
    protected $events = [];
    public function __construct(Dispatcher $dispatcher, $eventsToFake = [])
    {
        $this->dispatcher = $dispatcher;
        $this->eventsToFake = Arr::wrap($eventsToFake);
    }
    public function assertDispatched($event, $callback = null)
    {
        if (is_int($callback)) {
            return $this->assertDispatchedTimes($event, $callback);
        }
        PHPUnit::assertTrue(
            $this->dispatched($event, $callback)->count() > 0,
            "The expected [{$event}] event was not dispatched."
        );
    }
    public function assertDispatchedTimes($event, $times = 1)
    {
        PHPUnit::assertTrue(
            ($count = $this->dispatched($event)->count()) === $times,
            "The expected [{$event}] event was dispatched {$count} times instead of {$times} times."
        );
    }
    public function assertNotDispatched($event, $callback = null)
    {
        PHPUnit::assertTrue(
            $this->dispatched($event, $callback)->count() === 0,
            "The unexpected [{$event}] event was dispatched."
        );
    }
    public function dispatched($event, $callback = null)
    {
        if (! $this->hasDispatched($event)) {
            return collect();
        }
        $callback = $callback ?: function () {
            return true;
        };
        return collect($this->events[$event])->filter(function ($arguments) use ($callback) {
            return $callback(...$arguments);
        });
    }
    public function hasDispatched($event)
    {
        return isset($this->events[$event]) && ! empty($this->events[$event]);
    }
    public function listen($events, $listener)
    {
        $this->dispatcher->listen($events, $listener);
    }
    public function hasListeners($eventName)
    {
        return $this->dispatcher->hasListeners($eventName);
    }
    public function push($event, $payload = [])
    {
    }
    public function subscribe($subscriber)
    {
        $this->dispatcher->subscribe($subscriber);
    }
    public function flush($event)
    {
    }
    public function fire($event, $payload = [], $halt = false)
    {
        return $this->dispatch($event, $payload, $halt);
    }
    public function dispatch($event, $payload = [], $halt = false)
    {
        $name = is_object($event) ? get_class($event) : (string) $event;
        if ($this->shouldFakeEvent($name, $payload)) {
            $this->events[$name][] = func_get_args();
        } else {
            return $this->dispatcher->dispatch($event, $payload, $halt);
        }
    }
    protected function shouldFakeEvent($eventName, $payload)
    {
        if (empty($this->eventsToFake)) {
            return true;
        }
        return collect($this->eventsToFake)
            ->filter(function ($event) use ($eventName, $payload) {
                return $event instanceof Closure
                            ? $event($eventName, $payload)
                            : $event === $eventName;
            })
            ->isNotEmpty();
    }
    public function forget($event)
    {
    }
    public function forgetPushed()
    {
    }
    public function until($event, $payload = [])
    {
        return $this->dispatch($event, $payload, true);
    }
}
