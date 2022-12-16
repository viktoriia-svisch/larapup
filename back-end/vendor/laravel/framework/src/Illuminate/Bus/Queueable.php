<?php
namespace Illuminate\Bus;
trait Queueable
{
    public $connection;
    public $queue;
    public $chainConnection;
    public $chainQueue;
    public $delay;
    public $chained = [];
    public function onConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }
    public function onQueue($queue)
    {
        $this->queue = $queue;
        return $this;
    }
    public function allOnConnection($connection)
    {
        $this->chainConnection = $connection;
        $this->connection = $connection;
        return $this;
    }
    public function allOnQueue($queue)
    {
        $this->chainQueue = $queue;
        $this->queue = $queue;
        return $this;
    }
    public function delay($delay)
    {
        $this->delay = $delay;
        return $this;
    }
    public function chain($chain)
    {
        $this->chained = collect($chain)->map(function ($job) {
            return serialize($job);
        })->all();
        return $this;
    }
    public function dispatchNextJobInChain()
    {
        if (! empty($this->chained)) {
            dispatch(tap(unserialize(array_shift($this->chained)), function ($next) {
                $next->chained = $this->chained;
                $next->onConnection($next->connection ?: $this->chainConnection);
                $next->onQueue($next->queue ?: $this->chainQueue);
                $next->chainConnection = $this->chainConnection;
                $next->chainQueue = $this->chainQueue;
            }));
        }
    }
}
