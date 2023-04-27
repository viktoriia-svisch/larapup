<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
class OptimizeCommand extends Command
{
    protected $name = 'optimize';
    protected $description = 'Cache the framework bootstrap files';
    public function handle()
    {
        $this->call('config:cache');
        $this->call('route:cache');
        $this->info('Files cached successfully!');
    }
}
