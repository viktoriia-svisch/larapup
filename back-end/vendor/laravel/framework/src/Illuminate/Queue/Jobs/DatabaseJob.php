<?php
namespace Illuminate\Queue\Jobs;
use Illuminate\Container\Container;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Contracts\Queue\Job as JobContract;
class DatabaseJob extends Job implements JobContract
{
    protected $database;
    protected $job;
    public function __construct(Container $container, DatabaseQueue $database, $job, $connectionName, $queue)
    {
        $this->job = $job;
        $this->queue = $queue;
        $this->database = $database;
        $this->container = $container;
        $this->connectionName = $connectionName;
    }
    public function release($delay = 0)
    {
        parent::release($delay);
        $this->delete();
        return $this->database->release($this->queue, $this->job, $delay);
    }
    public function delete()
    {
        parent::delete();
        $this->database->deleteReserved($this->queue, $this->job->id);
    }
    public function attempts()
    {
        return (int) $this->job->attempts;
    }
    public function getJobId()
    {
        return $this->job->id;
    }
    public function getRawBody()
    {
        return $this->job->payload;
    }
}
