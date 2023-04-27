<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
class ConfigClearCommand extends Command
{
    protected $name = 'config:clear';
    protected $description = 'Remove the configuration cache file';
    protected $files;
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }
    public function handle()
    {
        $this->files->delete($this->laravel->getCachedConfigPath());
        $this->info('Configuration cache cleared!');
    }
}
