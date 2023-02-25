<?php
namespace Illuminate\Queue\Jobs;
use Pheanstalk\Pheanstalk;
use Illuminate\Container\Container;
use Pheanstalk\Job as PheanstalkJob;
use Illuminate\Contracts\Queue\Job as JobContract;
class BeanstalkdJob extends Job implements JobContract
{
    protected $pheanstalk;
    protected $job;
    public function __construct(Container $container, Pheanstalk $pheanstalk, PheanstalkJob $job, $connectionName, $queue)
    {
        $this->job = $job;
        $this->queue = $queue;
        $this->container = $container;
        $this->pheanstalk = $pheanstalk;
        $this->connectionName = $connectionName;
    }
    public function release($delay = 0)
    {
        parent::release($delay);
        $priority = Pheanstalk::DEFAULT_PRIORITY;
        $this->pheanstalk->release($this->job, $priority, $delay);
    }
    public function bury()
    {
        parent::release();
        $this->pheanstalk->bury($this->job);
    }
    public function delete()
    {
        parent::delete();
        $this->pheanstalk->delete($this->job);
    }
    public function attempts()
    {
        $stats = $this->pheanstalk->statsJob($this->job);
        return (int) $stats->reserves;
    }
    public function getJobId()
    {
        return $this->job->getId();
    }
    public function getRawBody()
    {
        return $this->job->getData();
    }
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }
    public function getPheanstalkJob()
    {
        return $this->job;
    }
}
