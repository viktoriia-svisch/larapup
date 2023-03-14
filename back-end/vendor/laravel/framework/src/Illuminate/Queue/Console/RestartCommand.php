<?php
namespace Illuminate\Queue\Console;
use Illuminate\Console\Command;
use Illuminate\Support\InteractsWithTime;
class RestartCommand extends Command
{
    use InteractsWithTime;
    protected $name = 'queue:restart';
    protected $description = 'Restart queue worker daemons after their current job';
    public function handle()
    {
        $this->laravel['cache']->forever('illuminate:queue:restart', $this->currentTime());
        $this->info('Broadcasting queue restart signal.');
    }
}
