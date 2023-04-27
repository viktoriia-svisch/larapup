<?php
namespace Illuminate\Queue\Console;
use Illuminate\Console\Command;
class ForgetFailedCommand extends Command
{
    protected $signature = 'queue:forget {id : The ID of the failed job}';
    protected $description = 'Delete a failed queue job';
    public function handle()
    {
        if ($this->laravel['queue.failer']->forget($this->argument('id'))) {
            $this->info('Failed job deleted successfully!');
        } else {
            $this->error('No failed job matches the given ID.');
        }
    }
}
