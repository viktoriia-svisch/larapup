<?php
namespace Illuminate\Queue\Jobs;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
class SyncJob extends Job implements JobContract
{
    protected $job;
    protected $payload;
    public function __construct(Container $container, $payload, $connectionName, $queue)
    {
        $this->queue = $queue;
        $this->payload = $payload;
        $this->container = $container;
        $this->connectionName = $connectionName;
    }
    public function release($delay = 0)
    {
        parent::release($delay);
    }
    public function attempts()
    {
        return 1;
    }
    public function getJobId()
    {
        return '';
    }
    public function getRawBody()
    {
        return $this->payload;
    }
    public function getQueue()
    {
        return 'sync';
    }
}
