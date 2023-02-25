<?php
namespace Illuminate\Support\Testing\Fakes;
use Illuminate\Queue\QueueManager;
use Illuminate\Contracts\Queue\Queue;
use PHPUnit\Framework\Assert as PHPUnit;
class QueueFake extends QueueManager implements Queue
{
    protected $jobs = [];
    public function assertPushed($job, $callback = null)
    {
        if (is_numeric($callback)) {
            return $this->assertPushedTimes($job, $callback);
        }
        PHPUnit::assertTrue(
            $this->pushed($job, $callback)->count() > 0,
            "The expected [{$job}] job was not pushed."
        );
    }
    protected function assertPushedTimes($job, $times = 1)
    {
        PHPUnit::assertTrue(
            ($count = $this->pushed($job)->count()) === $times,
            "The expected [{$job}] job was pushed {$count} times instead of {$times} times."
        );
    }
    public function assertPushedOn($queue, $job, $callback = null)
    {
        return $this->assertPushed($job, function ($job, $pushedQueue) use ($callback, $queue) {
            if ($pushedQueue !== $queue) {
                return false;
            }
            return $callback ? $callback(...func_get_args()) : true;
        });
    }
    public function assertPushedWithChain($job, $expectedChain = [], $callback = null)
    {
        PHPUnit::assertTrue(
            $this->pushed($job, $callback)->isNotEmpty(),
            "The expected [{$job}] job was not pushed."
        );
        PHPUnit::assertTrue(
            collect($expectedChain)->isNotEmpty(),
            'The expected chain can not be empty.'
        );
        $this->isChainOfObjects($expectedChain)
                ? $this->assertPushedWithChainOfObjects($job, $expectedChain, $callback)
                : $this->assertPushedWithChainOfClasses($job, $expectedChain, $callback);
    }
    protected function assertPushedWithChainOfObjects($job, $expectedChain, $callback)
    {
        $chain = collect($expectedChain)->map(function ($job) {
            return serialize($job);
        })->all();
        PHPUnit::assertTrue(
            $this->pushed($job, $callback)->filter(function ($job) use ($chain) {
                return $job->chained == $chain;
            })->isNotEmpty(),
            'The expected chain was not pushed.'
        );
    }
    protected function assertPushedWithChainOfClasses($job, $expectedChain, $callback)
    {
        $matching = $this->pushed($job, $callback)->map->chained->map(function ($chain) {
            return collect($chain)->map(function ($job) {
                return get_class(unserialize($job));
            });
        })->filter(function ($chain) use ($expectedChain) {
            return $chain->all() === $expectedChain;
        });
        PHPUnit::assertTrue(
            $matching->isNotEmpty(), 'The expected chain was not pushed.'
        );
    }
    protected function isChainOfObjects($chain)
    {
        return ! collect($chain)->contains(function ($job) {
            return ! is_object($job);
        });
    }
    public function assertNotPushed($job, $callback = null)
    {
        PHPUnit::assertTrue(
            $this->pushed($job, $callback)->count() === 0,
            "The unexpected [{$job}] job was pushed."
        );
    }
    public function assertNothingPushed()
    {
        PHPUnit::assertEmpty($this->jobs, 'Jobs were pushed unexpectedly.');
    }
    public function pushed($job, $callback = null)
    {
        if (! $this->hasPushed($job)) {
            return collect();
        }
        $callback = $callback ?: function () {
            return true;
        };
        return collect($this->jobs[$job])->filter(function ($data) use ($callback) {
            return $callback($data['job'], $data['queue']);
        })->pluck('job');
    }
    public function hasPushed($job)
    {
        return isset($this->jobs[$job]) && ! empty($this->jobs[$job]);
    }
    public function connection($value = null)
    {
        return $this;
    }
    public function size($queue = null)
    {
        return count($this->jobs);
    }
    public function push($job, $data = '', $queue = null)
    {
        $this->jobs[is_object($job) ? get_class($job) : $job][] = [
            'job' => $job,
            'queue' => $queue,
        ];
    }
    public function pushRaw($payload, $queue = null, array $options = [])
    {
    }
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->push($job, $data, $queue);
    }
    public function pushOn($queue, $job, $data = '')
    {
        return $this->push($job, $data, $queue);
    }
    public function laterOn($queue, $delay, $job, $data = '')
    {
        return $this->push($job, $data, $queue);
    }
    public function pop($queue = null)
    {
    }
    public function bulk($jobs, $data = '', $queue = null)
    {
        foreach ($jobs as $job) {
            $this->push($job, $data, $queue);
        }
    }
    public function pushedJobs()
    {
        return $this->jobs;
    }
    public function getConnectionName()
    {
    }
    public function setConnectionName($name)
    {
        return $this;
    }
}
