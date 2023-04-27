<?php
namespace Illuminate\Queue\Console;
use Illuminate\Queue\Listener;
use Illuminate\Console\Command;
use Illuminate\Queue\ListenerOptions;
class ListenCommand extends Command
{
    protected $signature = 'queue:listen
                            {connection? : The name of connection}
                            {--delay=0 : The number of seconds to delay failed jobs}
                            {--force : Force the worker to run even in maintenance mode}
                            {--memory=128 : The memory limit in megabytes}
                            {--queue= : The queue to listen on}
                            {--sleep=3 : Number of seconds to sleep when no job is available}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=0 : Number of times to attempt a job before logging it failed}';
    protected $description = 'Listen to a given queue';
    protected $listener;
    public function __construct(Listener $listener)
    {
        parent::__construct();
        $this->setOutputHandler($this->listener = $listener);
    }
    public function handle()
    {
        $queue = $this->getQueue(
            $connection = $this->input->getArgument('connection')
        );
        $this->listener->listen(
            $connection, $queue, $this->gatherOptions()
        );
    }
    protected function getQueue($connection)
    {
        $connection = $connection ?: $this->laravel['config']['queue.default'];
        return $this->input->getOption('queue') ?: $this->laravel['config']->get(
            "queue.connections.{$connection}.queue", 'default'
        );
    }
    protected function gatherOptions()
    {
        return new ListenerOptions(
            $this->option('env'), $this->option('delay'),
            $this->option('memory'), $this->option('timeout'),
            $this->option('sleep'), $this->option('tries'),
            $this->option('force')
        );
    }
    protected function setOutputHandler(Listener $listener)
    {
        $listener->setOutputHandler(function ($type, $line) {
            $this->output->write($line);
        });
    }
}
