<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
class AppNameCommand extends Command
{
    protected $name = 'app:name';
    protected $description = 'Set the application namespace';
    protected $composer;
    protected $files;
    protected $currentRoot;
    public function __construct(Composer $composer, Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
        $this->composer = $composer;
    }
    public function handle()
    {
        $this->currentRoot = trim($this->laravel->getNamespace(), '\\');
        $this->setAppDirectoryNamespace();
        $this->setBootstrapNamespaces();
        $this->setConfigNamespaces();
        $this->setComposerNamespace();
        $this->setDatabaseFactoryNamespaces();
        $this->info('Application namespace set!');
        $this->composer->dumpAutoloads();
        $this->call('optimize:clear');
    }
    protected function setAppDirectoryNamespace()
    {
        $files = Finder::create()
                            ->in($this->laravel['path'])
                            ->contains($this->currentRoot)
                            ->name('*.php');
        foreach ($files as $file) {
            $this->replaceNamespace($file->getRealPath());
        }
    }
    protected function replaceNamespace($path)
    {
        $search = [
            'namespace '.$this->currentRoot.';',
            $this->currentRoot.'\\',
        ];
        $replace = [
            'namespace '.$this->argument('name').';',
            $this->argument('name').'\\',
        ];
        $this->replaceIn($path, $search, $replace);
    }
    protected function setBootstrapNamespaces()
    {
        $search = [
            $this->currentRoot.'\\Http',
            $this->currentRoot.'\\Console',
            $this->currentRoot.'\\Exceptions',
        ];
        $replace = [
            $this->argument('name').'\\Http',
            $this->argument('name').'\\Console',
            $this->argument('name').'\\Exceptions',
        ];
        $this->replaceIn($this->getBootstrapPath(), $search, $replace);
    }
    protected function setConfigNamespaces()
    {
        $this->setAppConfigNamespaces();
        $this->setAuthConfigNamespace();
        $this->setServicesConfigNamespace();
    }
    protected function setAppConfigNamespaces()
    {
        $search = [
            $this->currentRoot.'\\Providers',
            $this->currentRoot.'\\Http\\Controllers\\',
        ];
        $replace = [
            $this->argument('name').'\\Providers',
            $this->argument('name').'\\Http\\Controllers\\',
        ];
        $this->replaceIn($this->getConfigPath('app'), $search, $replace);
    }
    protected function setAuthConfigNamespace()
    {
        $this->replaceIn(
            $this->getConfigPath('auth'),
            $this->currentRoot.'\\User',
            $this->argument('name').'\\User'
        );
    }
    protected function setServicesConfigNamespace()
    {
        $this->replaceIn(
            $this->getConfigPath('services'),
            $this->currentRoot.'\\User',
            $this->argument('name').'\\User'
        );
    }
    protected function setComposerNamespace()
    {
        $this->replaceIn(
            $this->getComposerPath(),
            str_replace('\\', '\\\\', $this->currentRoot).'\\\\',
            str_replace('\\', '\\\\', $this->argument('name')).'\\\\'
        );
    }
    protected function setDatabaseFactoryNamespaces()
    {
        $files = Finder::create()
                            ->in(database_path('factories'))
                            ->contains($this->currentRoot)
                            ->name('*.php');
        foreach ($files as $file) {
            $this->replaceIn(
                $file->getRealPath(),
                $this->currentRoot, $this->argument('name')
            );
        }
    }
    protected function replaceIn($path, $search, $replace)
    {
        if ($this->files->exists($path)) {
            $this->files->put($path, str_replace($search, $replace, $this->files->get($path)));
        }
    }
    protected function getBootstrapPath()
    {
        return $this->laravel->bootstrapPath().'/app.php';
    }
    protected function getComposerPath()
    {
        return base_path('composer.json');
    }
    protected function getConfigPath($name)
    {
        return $this->laravel['path.config'].'/'.$name.'.php';
    }
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The desired namespace'],
        ];
    }
}
