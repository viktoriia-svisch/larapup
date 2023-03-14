<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
class TestMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:test {name : The name of the class} {--unit : Create a unit test}';
    protected $description = 'Create a new test class';
    protected $type = 'Test';
    protected function getStub()
    {
        if ($this->option('unit')) {
            return __DIR__.'/stubs/unit-test.stub';
        }
        return __DIR__.'/stubs/test.stub';
    }
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        return base_path('tests').str_replace('\\', '/', $name).'.php';
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        if ($this->option('unit')) {
            return $rootNamespace.'\Unit';
        } else {
            return $rootNamespace.'\Feature';
        }
    }
    protected function rootNamespace()
    {
        return 'Tests';
    }
}
