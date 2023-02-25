<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
class OptimizeClearCommand extends Command
{
    protected $name = 'optimize:clear';
    protected $description = 'Remove the cached bootstrap files';
    public function handle()
    {
        $this->call('view:clear');
        $this->call('cache:clear');
        $this->call('route:clear');
        $this->call('config:clear');
        $this->call('clear-compiled');
        $this->info('Caches cleared successfully!');
    }
}
