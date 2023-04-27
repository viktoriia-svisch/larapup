<?php
namespace Illuminate\Queue\Console;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
class ListFailedCommand extends Command
{
    protected $name = 'queue:failed';
    protected $description = 'List all of the failed queue jobs';
    protected $headers = ['ID', 'Connection', 'Queue', 'Class', 'Failed At'];
    public function handle()
    {
        if (count($jobs = $this->getFailedJobs()) === 0) {
            return $this->info('No failed jobs!');
        }
        $this->displayFailedJobs($jobs);
    }
    protected function getFailedJobs()
    {
        $failed = $this->laravel['queue.failer']->all();
        return collect($failed)->map(function ($failed) {
            return $this->parseFailedJob((array) $failed);
        })->filter()->all();
    }
    protected function parseFailedJob(array $failed)
    {
        $row = array_values(Arr::except($failed, ['payload', 'exception']));
        array_splice($row, 3, 0, $this->extractJobName($failed['payload']));
        return $row;
    }
    private function extractJobName($payload)
    {
        $payload = json_decode($payload, true);
        if ($payload && (! isset($payload['data']['command']))) {
            return $payload['job'] ?? null;
        } elseif ($payload && isset($payload['data']['command'])) {
            return $this->matchJobName($payload);
        }
    }
    protected function matchJobName($payload)
    {
        preg_match('/"([^"]+)"/', $payload['data']['command'], $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return $payload['job'] ?? null;
    }
    protected function displayFailedJobs(array $jobs)
    {
        $this->table($this->headers, $jobs);
    }
}
