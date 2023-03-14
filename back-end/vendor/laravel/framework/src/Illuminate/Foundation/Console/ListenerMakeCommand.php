<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class ListenerMakeCommand extends GeneratorCommand
{
    protected $name = 'make:listener';
    protected $description = 'Create a new event listener class';
    protected $type = 'Listener';
    protected function buildClass($name)
    {
        $event = $this->option('event');
        if (! Str::startsWith($event, [
            $this->laravel->getNamespace(),
            'Illuminate',
            '\\',
        ])) {
            $event = $this->laravel->getNamespace().'Events\\'.$event;
        }
        $stub = str_replace(
            'DummyEvent', class_basename($event), parent::buildClass($name)
        );
        return str_replace(
            'DummyFullEvent', $event, $stub
        );
    }
    protected function getStub()
    {
        if ($this->option('queued')) {
            return $this->option('event')
                        ? __DIR__.'/stubs/listener-queued.stub'
                        : __DIR__.'/stubs/listener-queued-duck.stub';
        }
        return $this->option('event')
                    ? __DIR__.'/stubs/listener.stub'
                    : __DIR__.'/stubs/listener-duck.stub';
    }
    protected function alreadyExists($rawName)
    {
        return class_exists($rawName);
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Listeners';
    }
    protected function getOptions()
    {
        return [
            ['event', 'e', InputOption::VALUE_OPTIONAL, 'The event class being listened for'],
            ['queued', null, InputOption::VALUE_NONE, 'Indicates the event listener should be queued'],
        ];
    }
}
