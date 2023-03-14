<?php
namespace Illuminate\Queue;
use Exception;
use ReflectionClass;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class CallQueuedHandler
{
    protected $dispatcher;
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    public function call(Job $job, array $data)
    {
        try {
            $command = $this->setJobInstanceIfNecessary(
                $job, unserialize($data['command'])
            );
        } catch (ModelNotFoundException $e) {
            return $this->handleModelNotFound($job, $e);
        }
        $this->dispatcher->dispatchNow(
            $command, $this->resolveHandler($job, $command)
        );
        if (! $job->hasFailed() && ! $job->isReleased()) {
            $this->ensureNextJobInChainIsDispatched($command);
        }
        if (! $job->isDeletedOrReleased()) {
            $job->delete();
        }
    }
    protected function resolveHandler($job, $command)
    {
        $handler = $this->dispatcher->getCommandHandler($command) ?: null;
        if ($handler) {
            $this->setJobInstanceIfNecessary($job, $handler);
        }
        return $handler;
    }
    protected function setJobInstanceIfNecessary(Job $job, $instance)
    {
        if (in_array(InteractsWithQueue::class, class_uses_recursive($instance))) {
            $instance->setJob($job);
        }
        return $instance;
    }
    protected function ensureNextJobInChainIsDispatched($command)
    {
        if (method_exists($command, 'dispatchNextJobInChain')) {
            $command->dispatchNextJobInChain();
        }
    }
    protected function handleModelNotFound(Job $job, $e)
    {
        $class = $job->resolveName();
        try {
            $shouldDelete = (new ReflectionClass($class))
                    ->getDefaultProperties()['deleteWhenMissingModels'] ?? false;
        } catch (Exception $e) {
            $shouldDelete = false;
        }
        if ($shouldDelete) {
            return $job->delete();
        }
        return FailingJob::handle(
            $job->getConnectionName(), $job, $e
        );
    }
    public function failed(array $data, $e)
    {
        $command = unserialize($data['command']);
        if (method_exists($command, 'failed')) {
            $command->failed($e);
        }
    }
}
