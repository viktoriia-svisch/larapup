<?php
namespace Illuminate\Queue\Console;
use Illuminate\Console\Command;
class FlushFailedCommand extends Command
{
    protected $name = 'queue:flush';
    protected $description = 'Flush all of the failed queue jobs';
    public function handle()
    {
        $this->laravel['queue.failer']->flush();
        $this->info('All failed jobs deleted successfully!');
    }
}
