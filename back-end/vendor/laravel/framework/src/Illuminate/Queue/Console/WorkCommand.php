<?php
namespace Illuminate\Queue\Console;
use Illuminate\Queue\Worker;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
class WorkCommand extends Command
{
    protected $signature = 'queue:work
                            {connection? : The name of the queue connection to work}
                            {--queue= : The names of the queues to work}
                            {--daemon : Run the worker in daemon mode (Deprecated)}
                            {--once : Only process the next job on the queue}
                            {--stop-when-empty : Stop when the queue is empty}
                            {--delay=0 : The number of seconds to delay failed jobs}
                            {--force : Force the worker to run even in maintenance mode}
                            {--memory=128 : The memory limit in megabytes}
                            {--sleep=3 : Number of seconds to sleep when no job is available}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=0 : Number of times to attempt a job before logging it failed}';
    protected $description = 'Start processing jobs on the queue as a daemon';
    protected $worker;
    public function __construct(Worker $worker)
    {
        parent::__construct();
        $this->worker = $worker;
    }
    public function handle()
    {
        if ($this->downForMaintenance() && $this->option('once')) {
            return $this->worker->sleep($this->option('sleep'));
        }
        $this->listenForEvents();
        $connection = $this->argument('connection')
                        ?: $this->laravel['config']['queue.default'];
        $queue = $this->getQueue($connection);
        $this->runWorker(
            $connection, $queue
        );
    }
    protected function runWorker($connection, $queue)
    {
        $this->worker->setCache($this->laravel['cache']->driver());
        return $this->worker->{$this->option('once') ? 'runNextJob' : 'daemon'}(
            $connection, $queue, $this->gatherWorkerOptions()
        );
    }
    protected function gatherWorkerOptions()
    {
        return new WorkerOptions(
            $this->option('delay'), $this->option('memory'),
            $this->option('timeout'), $this->option('sleep'),
            $this->option('tries'), $this->option('force'),
            $this->option('stop-when-empty')
        );
    }
    protected function listenForEvents()
    {
        $this->laravel['events']->listen(JobProcessing::class, function ($event) {
            $this->writeOutput($event->job, 'starting');
        });
        $this->laravel['events']->listen(JobProcessed::class, function ($event) {
            $this->writeOutput($event->job, 'success');
        });
        $this->laravel['events']->listen(JobFailed::class, function ($event) {
            $this->writeOutput($event->job, 'failed');
            $this->logFailedJob($event);
        });
    }
    protected function writeOutput(Job $job, $status)
    {
        switch ($status) {
            case 'starting':
                return $this->writeStatus($job, 'Processing', 'comment');
            case 'success':
                return $this->writeStatus($job, 'Processed', 'info');
            case 'failed':
                return $this->writeStatus($job, 'Failed', 'error');
        }
    }
    protected function writeStatus(Job $job, $status, $type)
    {
        $this->output->writeln(sprintf(
            "<{$type}>[%s][%s] %s</{$type}> %s",
            Carbon::now()->format('Y-m-d H:i:s'),
            $job->getJobId(),
            str_pad("{$status}:", 11), $job->resolveName()
        ));
    }
    protected function logFailedJob(JobFailed $event)
    {
        $this->laravel['queue.failer']->log(
            $event->connectionName, $event->job->getQueue(),
            $event->job->getRawBody(), $event->exception
        );
    }
    protected function getQueue($connection)
    {
        return $this->option('queue') ?: $this->laravel['config']->get(
            "queue.connections.{$connection}.queue", 'default'
        );
    }
    protected function downForMaintenance()
    {
        return $this->option('force') ? false : $this->laravel->isDownForMaintenance();
    }
}
