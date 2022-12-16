<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
class EnvironmentCommand extends Command
{
    protected $name = 'env';
    protected $description = 'Display the current framework environment';
    public function handle()
    {
        $this->line('<info>Current application environment:</info> <comment>'.$this->laravel['env'].'</comment>');
    }
}
