<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
class UpCommand extends Command
{
    protected $name = 'up';
    protected $description = 'Bring the application out of maintenance mode';
    public function handle()
    {
        @unlink(storage_path('framework/down'));
        $this->info('Application is now live.');
    }
}
