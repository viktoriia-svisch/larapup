<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class ExceptionMakeCommand extends GeneratorCommand
{
    protected $name = 'make:exception';
    protected $description = 'Create a new custom exception class';
    protected $type = 'Exception';
    protected function getStub()
    {
        if ($this->option('render')) {
            return $this->option('report')
                ? __DIR__.'/stubs/exception-render-report.stub'
                : __DIR__.'/stubs/exception-render.stub';
        }
        return $this->option('report')
            ? __DIR__.'/stubs/exception-report.stub'
            : __DIR__.'/stubs/exception.stub';
    }
    protected function alreadyExists($rawName)
    {
        return class_exists($this->rootNamespace().'Exceptions\\'.$rawName);
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Exceptions';
    }
    protected function getOptions()
    {
        return [
            ['render', null, InputOption::VALUE_NONE, 'Create the exception with an empty render method'],
            ['report', null, InputOption::VALUE_NONE, 'Create the exception with an empty report method'],
        ];
    }
}
