<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class ObserverMakeCommand extends GeneratorCommand
{
    protected $name = 'make:observer';
    protected $description = 'Create a new observer class';
    protected $type = 'Observer';
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);
        $model = $this->option('model');
        return $model ? $this->replaceModel($stub, $model) : $stub;
    }
    protected function getStub()
    {
        return $this->option('model')
                    ? __DIR__.'/stubs/observer.stub'
                    : __DIR__.'/stubs/observer.plain.stub';
    }
    protected function replaceModel($stub, $model)
    {
        $model = str_replace('/', '\\', $model);
        $namespaceModel = $this->laravel->getNamespace().$model;
        if (Str::startsWith($model, '\\')) {
            $stub = str_replace('NamespacedDummyModel', trim($model, '\\'), $stub);
        } else {
            $stub = str_replace('NamespacedDummyModel', $namespaceModel, $stub);
        }
        $stub = str_replace(
            "use {$namespaceModel};\nuse {$namespaceModel};", "use {$namespaceModel};", $stub
        );
        $model = class_basename(trim($model, '\\'));
        $stub = str_replace('DocDummyModel', Str::snake($model, ' '), $stub);
        $stub = str_replace('DummyModel', $model, $stub);
        return str_replace('dummyModel', Str::camel($model), $stub);
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Observers';
    }
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The model that the observer applies to.'],
        ];
    }
}
