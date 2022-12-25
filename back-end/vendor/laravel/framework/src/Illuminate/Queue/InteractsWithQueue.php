<?php
namespace Illuminate\Queue;
use Illuminate\Contracts\Queue\Job as JobContract;
trait InteractsWithQueue
{
    protected $job;
    public function attempts()
    {
        return $this->job ? $this->job->attempts() : 1;
    }
    public function delete()
    {
        if ($this->job) {
            return $this->job->delete();
        }
    }
    public function fail($exception = null)
    {
        if ($this->job) {
            FailingJob::handle($this->job->getConnectionName(), $this->job, $exception);
        }
    }
    public function release($delay = 0)
    {
        if ($this->job) {
            return $this->job->release($delay);
        }
    }
    public function setJob(JobContract $job)
    {
        $this->job = $job;
        return $this;
    }
}
