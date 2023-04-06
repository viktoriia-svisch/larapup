<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class ModelMakeCommand extends GeneratorCommand
{
    protected $name = 'make:model';
    protected $description = 'Create a new Eloquent model class';
    protected $type = 'Model';
    public function handle()
    {
        if (parent::handle() === false && ! $this->option('force')) {
            return false;
        }
        if ($this->option('all')) {
            $this->input->setOption('factory', true);
            $this->input->setOption('migration', true);
            $this->input->setOption('controller', true);
            $this->input->setOption('resource', true);
        }
        if ($this->option('factory')) {
            $this->createFactory();
        }
        if ($this->option('migration')) {
            $this->createMigration();
        }
        if ($this->option('controller') || $this->option('resource')) {
            $this->createController();
        }
    }
    protected function createFactory()
    {
        $factory = Str::studly(class_basename($this->argument('name')));
        $this->call('make:factory', [
            'name' => "{$factory}Factory",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
    }
    protected function createMigration()
    {
        $table = Str::plural(Str::snake(class_basename($this->argument('name'))));
        if ($this->option('pivot')) {
            $table = Str::singular($table);
        }
        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }
    protected function createController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));
        $modelName = $this->qualifyClass($this->getNameInput());
        $this->call('make:controller', [
            'name' => "{$controller}Controller",
            '--model' => $this->option('resource') ? $modelName : null,
        ]);
    }
    protected function getStub()
    {
        if ($this->option('pivot')) {
            return __DIR__.'/stubs/pivot.model.stub';
        }
        return __DIR__.'/stubs/model.stub';
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }
    protected function getOptions()
    {
        return [
            ['all', 'a', InputOption::VALUE_NONE, 'Generate a migration, factory, and resource controller for the model'],
            ['controller', 'c', InputOption::VALUE_NONE, 'Create a new controller for the model'],
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model'],
            ['pivot', 'p', InputOption::VALUE_NONE, 'Indicates if the generated model should be a custom intermediate table model'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Indicates if the generated controller should be a resource controller'],
        ];
    }
}
