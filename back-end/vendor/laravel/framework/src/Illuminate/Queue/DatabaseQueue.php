<?php
namespace Illuminate\Queue;
use Illuminate\Support\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\DatabaseJobRecord;
use Illuminate\Contracts\Queue\Queue as QueueContract;
class DatabaseQueue extends Queue implements QueueContract
{
    protected $database;
    protected $table;
    protected $default;
    protected $retryAfter = 60;
    public function __construct(Connection $database, $table, $default = 'default', $retryAfter = 60)
    {
        $this->table = $table;
        $this->default = $default;
        $this->database = $database;
        $this->retryAfter = $retryAfter;
    }
    public function size($queue = null)
    {
        return $this->database->table($this->table)
                    ->where('queue', $this->getQueue($queue))
                    ->count();
    }
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($queue, $this->createPayload(
            $job, $this->getQueue($queue), $data
        ));
    }
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->pushToDatabase($queue, $payload);
    }
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($queue, $this->createPayload(
            $job, $this->getQueue($queue), $data
        ), $delay);
    }
    public function bulk($jobs, $data = '', $queue = null)
    {
        $queue = $this->getQueue($queue);
        $availableAt = $this->availableAt();
        return $this->database->table($this->table)->insert(collect((array) $jobs)->map(
            function ($job) use ($queue, $data, $availableAt) {
                return $this->buildDatabaseRecord($queue, $this->createPayload($job, $this->getQueue($queue), $data), $availableAt);
            }
        )->all());
    }
    public function release($queue, $job, $delay)
    {
        return $this->pushToDatabase($queue, $job->payload, $delay, $job->attempts);
    }
    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        return $this->database->table($this->table)->insertGetId($this->buildDatabaseRecord(
            $this->getQueue($queue), $payload, $this->availableAt($delay), $attempts
        ));
    }
    protected function buildDatabaseRecord($queue, $payload, $availableAt, $attempts = 0)
    {
        return [
            'queue' => $queue,
            'attempts' => $attempts,
            'reserved_at' => null,
            'available_at' => $availableAt,
            'created_at' => $this->currentTime(),
            'payload' => $payload,
        ];
    }
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);
        return $this->database->transaction(function () use ($queue) {
            if ($job = $this->getNextAvailableJob($queue)) {
                return $this->marshalJob($queue, $job);
            }
            return null;
        });
    }
    protected function getNextAvailableJob($queue)
    {
        $job = $this->database->table($this->table)
                    ->lockForUpdate()
                    ->where('queue', $this->getQueue($queue))
                    ->where(function ($query) {
                        $this->isAvailable($query);
                        $this->isReservedButExpired($query);
                    })
                    ->orderBy('id', 'asc')
                    ->first();
        return $job ? new DatabaseJobRecord((object) $job) : null;
    }
    protected function isAvailable($query)
    {
        $query->where(function ($query) {
            $query->whereNull('reserved_at')
                  ->where('available_at', '<=', $this->currentTime());
        });
    }
    protected function isReservedButExpired($query)
    {
        $expiration = Carbon::now()->subSeconds($this->retryAfter)->getTimestamp();
        $query->orWhere(function ($query) use ($expiration) {
            $query->where('reserved_at', '<=', $expiration);
        });
    }
    protected function marshalJob($queue, $job)
    {
        $job = $this->markJobAsReserved($job);
        return new DatabaseJob(
            $this->container, $this, $job, $this->connectionName, $queue
        );
    }
    protected function markJobAsReserved($job)
    {
        $this->database->table($this->table)->where('id', $job->id)->update([
            'reserved_at' => $job->touch(),
            'attempts' => $job->increment(),
        ]);
        return $job;
    }
    public function deleteReserved($queue, $id)
    {
        $this->database->transaction(function () use ($id) {
            if ($this->database->table($this->table)->lockForUpdate()->find($id)) {
                $this->database->table($this->table)->where('id', $id)->delete();
            }
        });
    }
    public function getQueue($queue)
    {
        return $queue ?: $this->default;
    }
    public function getDatabase()
    {
        return $this->database;
    }
}
