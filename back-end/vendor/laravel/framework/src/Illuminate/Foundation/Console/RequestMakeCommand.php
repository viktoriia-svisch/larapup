<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\GeneratorCommand;
class RequestMakeCommand extends GeneratorCommand
{
    protected $name = 'make:request';
    protected $description = 'Create a new form request class';
    protected $type = 'Request';
    protected function getStub()
    {
        return __DIR__.'/stubs/request.stub';
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Requests';
    }
}
