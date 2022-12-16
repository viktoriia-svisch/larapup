<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Illuminate\Support\InteractsWithTime;
class DownCommand extends Command
{
    use InteractsWithTime;
    protected $signature = 'down {--message= : The message for the maintenance mode}
                                 {--retry= : The number of seconds after which the request may be retried}
                                 {--allow=* : IP or networks allowed to access the application while in maintenance mode}';
    protected $description = 'Put the application into maintenance mode';
    public function handle()
    {
        file_put_contents(
            storage_path('framework/down'),
            json_encode($this->getDownFilePayload(), JSON_PRETTY_PRINT)
        );
        $this->comment('Application is now in maintenance mode.');
    }
    protected function getDownFilePayload()
    {
        return [
            'time' => $this->currentTime(),
            'message' => $this->option('message'),
            'retry' => $this->getRetryTime(),
            'allowed' => $this->option('allow'),
        ];
    }
    protected function getRetryTime()
    {
        $retry = $this->option('retry');
        return is_numeric($retry) && $retry > 0 ? (int) $retry : null;
    }
}
