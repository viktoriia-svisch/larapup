<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class NotificationMakeCommand extends GeneratorCommand
{
    protected $name = 'make:notification';
    protected $description = 'Create a new notification class';
    protected $type = 'Notification';
    public function handle()
    {
        if (parent::handle() === false && ! $this->option('force')) {
            return;
        }
        if ($this->option('markdown')) {
            $this->writeMarkdownTemplate();
        }
    }
    protected function writeMarkdownTemplate()
    {
        $path = resource_path('views/'.str_replace('.', '/', $this->option('markdown'))).'.blade.php';
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true);
        }
        $this->files->put($path, file_get_contents(__DIR__.'/stubs/markdown.stub'));
    }
    protected function buildClass($name)
    {
        $class = parent::buildClass($name);
        if ($this->option('markdown')) {
            $class = str_replace('DummyView', $this->option('markdown'), $class);
        }
        return $class;
    }
    protected function getStub()
    {
        return $this->option('markdown')
                        ? __DIR__.'/stubs/markdown-notification.stub'
                        : __DIR__.'/stubs/notification.stub';
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Notifications';
    }
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the notification already exists'],
            ['markdown', 'm', InputOption::VALUE_OPTIONAL, 'Create a new Markdown template for the notification'],
        ];
    }
}
