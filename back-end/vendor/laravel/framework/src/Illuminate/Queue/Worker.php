<?php
namespace Illuminate\Queue;
use Exception;
use Throwable;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DetectsLostConnections;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Illuminate\Contracts\Cache\Repository as CacheContract;
class Worker
{
    use DetectsLostConnections;
    protected $manager;
    protected $events;
    protected $cache;
    protected $exceptions;
    public $shouldQuit = false;
    public $paused = false;
    public function __construct(QueueManager $manager,
                                Dispatcher $events,
                                ExceptionHandler $exceptions)
    {
        $this->events = $events;
        $this->manager = $manager;
        $this->exceptions = $exceptions;
    }
    public function daemon($connectionName, $queue, WorkerOptions $options)
    {
        if ($this->supportsAsyncSignals()) {
            $this->listenForSignals();
        }
        $lastRestart = $this->getTimestampOfLastQueueRestart();
        while (true) {
            if (! $this->daemonShouldRun($options, $connectionName, $queue)) {
                $this->pauseWorker($options, $lastRestart);
                continue;
            }
            $job = $this->getNextJob(
                $this->manager->connection($connectionName), $queue
            );
            if ($this->supportsAsyncSignals()) {
                $this->registerTimeoutHandler($job, $options);
            }
            if ($job) {
                $this->runJob($job, $connectionName, $options);
            } else {
                $this->sleep($options->sleep);
            }
            $this->stopIfNecessary($options, $lastRestart, $job);
        }
    }
    protected function registerTimeoutHandler($job, WorkerOptions $options)
    {
        pcntl_signal(SIGALRM, function () {
            $this->kill(1);
        });
        pcntl_alarm(
            max($this->timeoutForJob($job, $options), 0)
        );
    }
    protected function timeoutForJob($job, WorkerOptions $options)
    {
        return $job && ! is_null($job->timeout()) ? $job->timeout() : $options->timeout;
    }
    protected function daemonShouldRun(WorkerOptions $options, $connectionName, $queue)
    {
        return ! (($this->manager->isDownForMaintenance() && ! $options->force) ||
            $this->paused ||
            $this->events->until(new Events\Looping($connectionName, $queue)) === false);
    }
    protected function pauseWorker(WorkerOptions $options, $lastRestart)
    {
        $this->sleep($options->sleep > 0 ? $options->sleep : 1);
        $this->stopIfNecessary($options, $lastRestart);
    }
    protected function stopIfNecessary(WorkerOptions $options, $lastRestart, $job = null)
    {
        if ($this->shouldQuit) {
            $this->stop();
        } elseif ($this->memoryExceeded($options->memory)) {
            $this->stop(12);
        } elseif ($this->queueShouldRestart($lastRestart)) {
            $this->stop();
        } elseif ($options->stopWhenEmpty && is_null($job)) {
            $this->stop();
        }
    }
    public function runNextJob($connectionName, $queue, WorkerOptions $options)
    {
        $job = $this->getNextJob(
            $this->manager->connection($connectionName), $queue
        );
        if ($job) {
            return $this->runJob($job, $connectionName, $options);
        }
        $this->sleep($options->sleep);
    }
    protected function getNextJob($connection, $queue)
    {
        try {
            foreach (explode(',', $queue) as $queue) {
                if (! is_null($job = $connection->pop($queue))) {
                    return $job;
                }
            }
        } catch (Exception $e) {
            $this->exceptions->report($e);
            $this->stopWorkerIfLostConnection($e);
            $this->sleep(1);
        } catch (Throwable $e) {
            $this->exceptions->report($e = new FatalThrowableError($e));
            $this->stopWorkerIfLostConnection($e);
            $this->sleep(1);
        }
    }
    protected function runJob($job, $connectionName, WorkerOptions $options)
    {
        try {
            return $this->process($connectionName, $job, $options);
        } catch (Exception $e) {
            $this->exceptions->report($e);
            $this->stopWorkerIfLostConnection($e);
        } catch (Throwable $e) {
            $this->exceptions->report($e = new FatalThrowableError($e));
            $this->stopWorkerIfLostConnection($e);
        }
    }
    protected function stopWorkerIfLostConnection($e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->shouldQuit = true;
        }
    }
    public function process($connectionName, $job, WorkerOptions $options)
    {
        try {
            $this->raiseBeforeJobEvent($connectionName, $job);
            $this->markJobAsFailedIfAlreadyExceedsMaxAttempts(
                $connectionName, $job, (int) $options->maxTries
            );
            $job->fire();
            $this->raiseAfterJobEvent($connectionName, $job);
        } catch (Exception $e) {
            $this->handleJobException($connectionName, $job, $options, $e);
        } catch (Throwable $e) {
            $this->handleJobException(
                $connectionName, $job, $options, new FatalThrowableError($e)
            );
        }
    }
    protected function handleJobException($connectionName, $job, WorkerOptions $options, $e)
    {
        try {
            if (! $job->hasFailed()) {
                $this->markJobAsFailedIfWillExceedMaxAttempts(
                    $connectionName, $job, (int) $options->maxTries, $e
                );
            }
            $this->raiseExceptionOccurredJobEvent(
                $connectionName, $job, $e
            );
        } finally {
            if (! $job->isDeleted() && ! $job->isReleased() && ! $job->hasFailed()) {
                $job->release($options->delay);
            }
        }
        throw $e;
    }
    protected function markJobAsFailedIfAlreadyExceedsMaxAttempts($connectionName, $job, $maxTries)
    {
        $maxTries = ! is_null($job->maxTries()) ? $job->maxTries() : $maxTries;
        $timeoutAt = $job->timeoutAt();
        if ($timeoutAt && Carbon::now()->getTimestamp() <= $timeoutAt) {
            return;
        }
        if (! $timeoutAt && ($maxTries === 0 || $job->attempts() <= $maxTries)) {
            return;
        }
        $this->failJob($connectionName, $job, $e = new MaxAttemptsExceededException(
            $job->resolveName().' has been attempted too many times or run too long. The job may have previously timed out.'
        ));
        throw $e;
    }
    protected function markJobAsFailedIfWillExceedMaxAttempts($connectionName, $job, $maxTries, $e)
    {
        $maxTries = ! is_null($job->maxTries()) ? $job->maxTries() : $maxTries;
        if ($job->timeoutAt() && $job->timeoutAt() <= Carbon::now()->getTimestamp()) {
            $this->failJob($connectionName, $job, $e);
        }
        if ($maxTries > 0 && $job->attempts() >= $maxTries) {
            $this->failJob($connectionName, $job, $e);
        }
    }
    protected function failJob($connectionName, $job, $e)
    {
        return FailingJob::handle($connectionName, $job, $e);
    }
    protected function raiseBeforeJobEvent($connectionName, $job)
    {
        $this->events->dispatch(new Events\JobProcessing(
            $connectionName, $job
        ));
    }
    protected function raiseAfterJobEvent($connectionName, $job)
    {
        $this->events->dispatch(new Events\JobProcessed(
            $connectionName, $job
        ));
    }
    protected function raiseExceptionOccurredJobEvent($connectionName, $job, $e)
    {
        $this->events->dispatch(new Events\JobExceptionOccurred(
            $connectionName, $job, $e
        ));
    }
    protected function queueShouldRestart($lastRestart)
    {
        return $this->getTimestampOfLastQueueRestart() != $lastRestart;
    }
    protected function getTimestampOfLastQueueRestart()
    {
        if ($this->cache) {
            return $this->cache->get('illuminate:queue:restart');
        }
    }
    protected function listenForSignals()
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, function () {
            $this->shouldQuit = true;
        });
        pcntl_signal(SIGUSR2, function () {
            $this->paused = true;
        });
        pcntl_signal(SIGCONT, function () {
            $this->paused = false;
        });
    }
    protected function supportsAsyncSignals()
    {
        return extension_loaded('pcntl');
    }
    public function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage(true) / 1024 / 1024) >= $memoryLimit;
    }
    public function stop($status = 0)
    {
        $this->events->dispatch(new Events\WorkerStopping($status));
        exit($status);
    }
    public function kill($status = 0)
    {
        $this->events->dispatch(new Events\WorkerStopping($status));
        if (extension_loaded('posix')) {
            posix_kill(getmypid(), SIGKILL);
        }
        exit($status);
    }
    public function sleep($seconds)
    {
        if ($seconds < 1) {
            usleep($seconds * 1000000);
        } else {
            sleep($seconds);
        }
    }
    public function setCache(CacheContract $cache)
    {
        $this->cache = $cache;
    }
    public function getManager()
    {
        return $this->manager;
    }
    public function setManager(QueueManager $manager)
    {
        $this->manager = $manager;
    }
}
