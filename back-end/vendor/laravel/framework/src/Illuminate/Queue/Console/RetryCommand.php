<?php
namespace Illuminate\Queue\Console;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
class RetryCommand extends Command
{
    protected $signature = 'queue:retry {id* : The ID of the failed job or "all" to retry all jobs}';
    protected $description = 'Retry a failed queue job';
    public function handle()
    {
        foreach ($this->getJobIds() as $id) {
            $job = $this->laravel['queue.failer']->find($id);
            if (is_null($job)) {
                $this->error("Unable to find failed job with ID [{$id}].");
            } else {
                $this->retryJob($job);
                $this->info("The failed job [{$id}] has been pushed back onto the queue!");
                $this->laravel['queue.failer']->forget($id);
            }
        }
    }
    protected function getJobIds()
    {
        $ids = (array) $this->argument('id');
        if (count($ids) === 1 && $ids[0] === 'all') {
            $ids = Arr::pluck($this->laravel['queue.failer']->all(), 'id');
        }
        return $ids;
    }
    protected function retryJob($job)
    {
        $this->laravel['queue']->connection($job->connection)->pushRaw(
            $this->resetAttempts($job->payload), $job->queue
        );
    }
    protected function resetAttempts($payload)
    {
        $payload = json_decode($payload, true);
        if (isset($payload['attempts'])) {
            $payload['attempts'] = 0;
        }
        return json_encode($payload);
    }
}
