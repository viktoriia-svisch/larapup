<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\GeneratorCommand;
class EventMakeCommand extends GeneratorCommand
{
    protected $name = 'make:event';
    protected $description = 'Create a new event class';
    protected $type = 'Event';
    protected function alreadyExists($rawName)
    {
        return class_exists($rawName);
    }
    protected function getStub()
    {
        return __DIR__.'/stubs/event.stub';
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Events';
    }
}
