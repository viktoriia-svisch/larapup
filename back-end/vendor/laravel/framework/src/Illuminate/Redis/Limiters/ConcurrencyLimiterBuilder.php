<?php
namespace Illuminate\Redis\Limiters;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Contracts\Redis\LimiterTimeoutException;
class ConcurrencyLimiterBuilder
{
    use InteractsWithTime;
    public $connection;
    public $name;
    public $maxLocks;
    public $releaseAfter = 60;
    public $timeout = 3;
    public function __construct($connection, $name)
    {
        $this->name = $name;
        $this->connection = $connection;
    }
    public function limit($maxLocks)
    {
        $this->maxLocks = $maxLocks;
        return $this;
    }
    public function releaseAfter($releaseAfter)
    {
        $this->releaseAfter = $this->secondsUntil($releaseAfter);
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
            return (new ConcurrencyLimiter(
                $this->connection, $this->name, $this->maxLocks, $this->releaseAfter
            ))->block($this->timeout, $callback);
        } catch (LimiterTimeoutException $e) {
            if ($failure) {
                return $failure($e);
            }
            throw $e;
        }
    }
}
