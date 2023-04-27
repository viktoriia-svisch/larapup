<?php
namespace Illuminate\Queue;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Job as PheanstalkJob;
use Illuminate\Queue\Jobs\BeanstalkdJob;
use Pheanstalk\Contract\PheanstalkInterface;
use Illuminate\Contracts\Queue\Queue as QueueContract;
class BeanstalkdQueue extends Queue implements QueueContract
{
    protected $pheanstalk;
    protected $default;
    protected $timeToRun;
    public function __construct(Pheanstalk $pheanstalk, $default, $timeToRun)
    {
        $this->default = $default;
        $this->timeToRun = $timeToRun;
        $this->pheanstalk = $pheanstalk;
    }
    public function size($queue = null)
    {
        $queue = $this->getQueue($queue);
        return (int) $this->pheanstalk->statsTube($queue)->current_jobs_ready;
    }
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $this->getQueue($queue), $data), $queue);
    }
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->pheanstalk->useTube($this->getQueue($queue))->put(
            $payload, Pheanstalk::DEFAULT_PRIORITY, Pheanstalk::DEFAULT_DELAY, $this->timeToRun
        );
    }
    public function later($delay, $job, $data = '', $queue = null)
    {
        $pheanstalk = $this->pheanstalk->useTube($this->getQueue($queue));
        return $pheanstalk->put(
            $this->createPayload($job, $this->getQueue($queue), $data),
            Pheanstalk::DEFAULT_PRIORITY,
            $this->secondsUntil($delay),
            $this->timeToRun
        );
    }
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);
        $this->pheanstalk->watchOnly($queue);
        $job = interface_exists(PheanstalkInterface::class)
                    ? $this->pheanstalk->reserveWithTimeout(0)
                    : $this->pheanstalk->reserve(0);
        if ($job instanceof PheanstalkJob) {
            return new BeanstalkdJob(
                $this->container, $this->pheanstalk, $job, $this->connectionName, $queue
            );
        }
    }
    public function deleteMessage($queue, $id)
    {
        $queue = $this->getQueue($queue);
        $this->pheanstalk->useTube($queue)->delete(new PheanstalkJob($id, ''));
    }
    public function getQueue($queue)
    {
        return $queue ?: $this->default;
    }
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }
}
