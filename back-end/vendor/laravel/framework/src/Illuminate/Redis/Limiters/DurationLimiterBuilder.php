<?php
namespace Illuminate\Redis\Limiters;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Contracts\Redis\LimiterTimeoutException;
class DurationLimiterBuilder
{
    use InteractsWithTime;
    public $connection;
    public $name;
    public $maxLocks;
    public $decay;
    public $timeout = 3;
    public function __construct($connection, $name)
    {
        $this->name = $name;
        $this->connection = $connection;
    }
    public function allow($maxLocks)
    {
        $this->maxLocks = $maxLocks;
        return $this;
    }
    public function every($decay)
    {
        $this->decay = $this->secondsUntil($decay);
        return $this;
    }
    public function block($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }
    public function then(callable $callback, callable $failure = null)
    {
        try {
            return (new DurationLimiter(
                $this->connection, $this->name, $this->maxLocks, $this->decay
            ))->block($this->timeout, $callback);
        } catch (LimiterTimeoutException $e) {
            if ($failure) {
                return $failure($e);
            }
            throw $e;
        }
    }
}
