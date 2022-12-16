<?php
namespace Illuminate\Auth\Console;
use Illuminate\Console\Command;
class ClearResetsCommand extends Command
{
    protected $signature = 'auth:clear-resets {name? : The name of the password broker}';
    protected $description = 'Flush expired password reset tokens';
    public function handle()
    {
        $this->laravel['auth.password']->broker($this->argument('name'))->getRepository()->deleteExpired();
        $this->info('Expired reset tokens cleared!');
    }
}
