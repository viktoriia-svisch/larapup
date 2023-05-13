<?php
namespace Illuminate\Foundation\Bus;
use Illuminate\Contracts\Bus\Dispatcher;
class PendingDispatch
{
    protected $job;
    public function __construct($job)
    {
        $this->job = $job;
    }
    public function onConnection($connection)
    {
        $this->job->onConnection($connection);
        return $this;
    }
    public function onQueue($queue)
    {
        $this->job->onQueue($queue);
        return $this;
    }
    public function allOnConnection($connection)
    {
        $this->job->allOnConnection($connection);
        return $this;
    }
    public function allOnQueue($queue)
    {
        $this->job->allOnQueue($queue);
        return $this;
    }
    public function delay($delay)
    {
        $this->job->delay($delay);
        return $this;
    }
    public function chain($chain)
    {
        $this->job->chain($chain);
        return $this;
    }
    public function __destruct()
    {
        app(Dispatcher::class)->dispatch($this->job);
    }
}
