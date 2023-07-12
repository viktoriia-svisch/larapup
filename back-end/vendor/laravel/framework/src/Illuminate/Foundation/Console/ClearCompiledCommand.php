<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
class ClearCompiledCommand extends Command
{
    protected $name = 'clear-compiled';
    protected $description = 'Remove the compiled class file';
    public function handle()
    {
        if (file_exists($servicesPath = $this->laravel->getCachedServicesPath())) {
            @unlink($servicesPath);
        }
        if (file_exists($packagesPath = $this->laravel->getCachedPackagesPath())) {
            @unlink($packagesPath);
        }
        $this->info('Compiled services and packages files removed!');
    }
}
