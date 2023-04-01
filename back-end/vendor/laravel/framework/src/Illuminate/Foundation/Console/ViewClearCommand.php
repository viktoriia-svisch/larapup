<?php
namespace Illuminate\Foundation\Console;
use RuntimeException;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
class ViewClearCommand extends Command
{
    protected $name = 'view:clear';
    protected $description = 'Clear all compiled view files';
    protected $files;
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }
    public function handle()
    {
        $path = $this->laravel['config']['view.compiled'];
        if (! $path) {
            throw new RuntimeException('View path not found.');
        }
        foreach ($this->files->glob("{$path}/*") as $view) {
            $this->files->delete($view);
        }
        $this->info('Compiled views cleared!');
    }
}
