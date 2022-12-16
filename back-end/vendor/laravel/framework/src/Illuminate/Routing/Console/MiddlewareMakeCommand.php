<?php
namespace Illuminate\Routing\Console;
use Illuminate\Console\GeneratorCommand;
class MiddlewareMakeCommand extends GeneratorCommand
{
    protected $name = 'make:middleware';
    protected $description = 'Create a new middleware class';
    protected $type = 'Middleware';
    protected function getStub()
    {
        return __DIR__.'/stubs/middleware.stub';
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Middleware';
    }
}
