<?php
namespace Illuminate\Database\Console\Factories;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class FactoryMakeCommand extends GeneratorCommand
{
    protected $name = 'make:factory';
    protected $description = 'Create a new model factory';
    protected $type = 'Factory';
    protected function getStub()
    {
        return __DIR__.'/stubs/factory.stub';
    }
    protected function buildClass($name)
    {
        $model = $this->option('model')
                        ? $this->qualifyClass($this->option('model'))
                        : 'Model';
        return str_replace(
            'DummyModel', $model, parent::buildClass($name)
        );
    }
    protected function getPath($name)
    {
        $name = str_replace(
            ['\\', '/'], '', $this->argument('name')
        );
        return $this->laravel->databasePath()."/factories/{$name}.php";
    }
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The name of the model'],
        ];
    }
}
