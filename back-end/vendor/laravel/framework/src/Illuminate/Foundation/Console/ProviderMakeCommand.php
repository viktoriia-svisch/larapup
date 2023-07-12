<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\GeneratorCommand;
class ProviderMakeCommand extends GeneratorCommand
{
    protected $name = 'make:provider';
    protected $description = 'Create a new service provider class';
    protected $type = 'Provider';
    protected function getStub()
    {
        return __DIR__.'/stubs/provider.stub';
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Providers';
    }
}
