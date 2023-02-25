<?php
namespace Illuminate\Queue;
use DateTimeInterface;
use Illuminate\Container\Container;
use Illuminate\Support\InteractsWithTime;
abstract class Queue
{
    use InteractsWithTime;
    protected $container;
    protected $connectionName;
    protected static $createPayloadCallbacks = [];
    public function pushOn($queue, $job, $data = '')
    {
        return $this->push($job, $data, $queue);
    }
    public function laterOn($queue, $delay, $job, $data = '')
    {
        return $this->later($delay, $job, $data, $queue);
    }
    public function bulk($jobs, $data = '', $queue = null)
    {
        foreach ((array) $jobs as $job) {
            $this->push($job, $data, $queue);
        }
    }
    protected function createPayload($job, $queue, $data = '')
    {
        $payload = json_encode($this->createPayloadArray($job, $queue, $data));
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidPayloadException(
                'Unable to JSON encode payload. Error code: '.json_last_error()
            );
        }
        return $payload;
    }
    protected function createPayloadArray($job, $queue, $data = '')
    {
        return is_object($job)
                    ? $this->createObjectPayload($job, $queue)
                    : $this->createStringPayload($job, $queue, $data);
    }
    protected function createObjectPayload($job, $queue)
    {
        $payload = $this->withCreatePayloadHooks($queue, [
            'displayName' => $this->getDisplayName($job),
            'job' => 'Illuminate\Queue\CallQueuedHandler@call',
            'maxTries' => $job->tries ?? null,
            'timeout' => $job->timeout ?? null,
            'timeoutAt' => $this->getJobExpiration($job),
            'data' => [
                'commandName' => $job,
                'command' => $job,
            ],
        ]);
        return array_merge($payload, [
            'data' => [
                'commandName' => get_class($job),
                'command' => serialize(clone $job),
            ],
        ]);
    }
    protected function getDisplayName($job)
    {
        return method_exists($job, 'displayName')
                        ? $job->displayName() : get_class($job);
    }
    public function getJobExpiration($job)
    {
        if (! method_exists($job, 'retryUntil') && ! isset($job->timeoutAt)) {
            return;
        }
        $expiration = $job->timeoutAt ?? $job->retryUntil();
        return $expiration instanceof DateTimeInterface
                        ? $expiration->getTimestamp() : $expiration;
    }
    protected function createStringPayload($job, $queue, $data)
    {
        return $this->withCreatePayloadHooks($queue, [
            'displayName' => is_string($job) ? explode('@', $job)[0] : null,
            'job' => $job,
            'maxTries' => null,
            'timeout' => null,
            'data' => $data,
        ]);
    }
    public static function createPayloadUsing($callback)
    {
        if (is_null($callback)) {
            static::$createPayloadCallbacks = [];
        } else {
            static::$createPayloadCallbacks[] = $callback;
        }
    }
    protected function withCreatePayloadHooks($queue, array $payload)
    {
        if (! empty(static::$createPayloadCallbacks)) {
            foreach (static::$createPayloadCallbacks as $callback) {
                $payload = array_merge($payload, call_user_func(
                    $callback, $this->getConnectionName(), $queue, $payload
                ));
            }
        }
        return $payload;
    }
    public function getConnectionName()
    {
        return $this->connectionName;
    }
    public function setConnectionName($name)
    {
        $this->connectionName = $name;
        return $this;
    }
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}
