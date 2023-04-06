<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\GeneratorCommand;
class RuleMakeCommand extends GeneratorCommand
{
    protected $name = 'make:rule';
    protected $description = 'Create a new validation rule';
    protected $type = 'Rule';
    protected function getStub()
    {
        return __DIR__.'/stubs/rule.stub';
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Rules';
    }
}
