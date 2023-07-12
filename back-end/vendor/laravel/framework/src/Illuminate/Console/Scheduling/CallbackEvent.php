<?php
namespace Illuminate\Console\Scheduling;
use LogicException;
use InvalidArgumentException;
use Illuminate\Contracts\Container\Container;
class CallbackEvent extends Event
{
    protected $callback;
    protected $parameters;
    public function __construct(EventMutex $mutex, $callback, array $parameters = [])
    {
        if (! is_string($callback) && ! is_callable($callback)) {
            throw new InvalidArgumentException(
                'Invalid scheduled callback event. Must be a string or callable.'
            );
        }
        $this->mutex = $mutex;
        $this->callback = $callback;
        $this->parameters = $parameters;
    }
    public function run(Container $container)
    {
        if ($this->description && $this->withoutOverlapping &&
            ! $this->mutex->create($this)) {
            return;
        }
        $pid = getmypid();
        register_shutdown_function(function () use ($pid) {
            if ($pid === getmypid()) {
                $this->removeMutex();
            }
        });
        parent::callBeforeCallbacks($container);
        try {
            $response = is_object($this->callback)
                        ? $container->call([$this->callback, '__invoke'], $this->parameters)
                        : $container->call($this->callback, $this->parameters);
        } finally {
            $this->removeMutex();
            parent::callAfterCallbacks($container);
        }
        return $response;
    }
    protected function removeMutex()
    {
        if ($this->description) {
            $this->mutex->forget($this);
        }
    }
    public function withoutOverlapping($expiresAt = 1440)
    {
        if (! isset($this->description)) {
            throw new LogicException(
                "A scheduled event name is required to prevent overlapping. Use the 'name' method before 'withoutOverlapping'."
            );
        }
        $this->withoutOverlapping = true;
        $this->expiresAt = $expiresAt;
        return $this->skip(function () {
            return $this->mutex->exists($this);
        });
    }
    public function onOneServer()
    {
        if (! isset($this->description)) {
            throw new LogicException(
                "A scheduled event name is required to only run on one server. Use the 'name' method before 'onOneServer'."
            );
        }
        $this->onOneServer = true;
        return $this;
    }
    public function mutexName()
    {
        return 'framework/schedule-'.sha1($this->description);
    }
    public function getSummaryForDisplay()
    {
        if (is_string($this->description)) {
            return $this->description;
        }
        return is_string($this->callback) ? $this->callback : 'Closure';
    }
}
