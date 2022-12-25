<?php
namespace Illuminate\Queue;
use Exception;
use Throwable;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Symfony\Component\Debug\Exception\FatalThrowableError;
class SyncQueue extends Queue implements QueueContract
{
    public function size($queue = null)
    {
        return 0;
    }
    public function push($job, $data = '', $queue = null)
    {
        $queueJob = $this->resolveJob($this->createPayload($job, $queue, $data), $queue);
        try {
            $this->raiseBeforeJobEvent($queueJob);
            $queueJob->fire();
            $this->raiseAfterJobEvent($queueJob);
        } catch (Exception $e) {
            $this->handleException($queueJob, $e);
        } catch (Throwable $e) {
            $this->handleException($queueJob, new FatalThrowableError($e));
        }
        return 0;
    }
    protected function resolveJob($payload, $queue)
    {
        return new SyncJob($this->container, $payload, $this->connectionName, $queue);
    }
    protected function raiseBeforeJobEvent(Job $job)
    {
        if ($this->container->bound('events')) {
            $this->container['events']->dispatch(new Events\JobProcessing($this->connectionName, $job));
        }
    }
    protected function raiseAfterJobEvent(Job $job)
    {
        if ($this->container->bound('events')) {
            $this->container['events']->dispatch(new Events\JobProcessed($this->connectionName, $job));
        }
    }
    protected function raiseExceptionOccurredJobEvent(Job $job, $e)
    {
        if ($this->container->bound('events')) {
            $this->container['events']->dispatch(new Events\JobExceptionOccurred($this->connectionName, $job, $e));
        }
    }
    protected function handleException($queueJob, $e)
    {
        $this->raiseExceptionOccurredJobEvent($queueJob, $e);
        FailingJob::handle($this->connectionName, $queueJob, $e);
        throw $e;
    }
    public function pushRaw($payload, $queue = null, array $options = [])
    {
    }
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->push($job, $data, $queue);
    }
    public function pop($queue = null)
    {
    }
}
