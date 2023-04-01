<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class ResourceMakeCommand extends GeneratorCommand
{
    protected $name = 'make:resource';
    protected $description = 'Create a new resource';
    protected $type = 'Resource';
    public function handle()
    {
        if ($this->collection()) {
            $this->type = 'Resource collection';
        }
        parent::handle();
    }
    protected function getStub()
    {
        return $this->collection()
                    ? __DIR__.'/stubs/resource-collection.stub'
                    : __DIR__.'/stubs/resource.stub';
    }
    protected function collection()
    {
        return $this->option('collection') ||
               Str::endsWith($this->argument('name'), 'Collection');
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Resources';
    }
    protected function getOptions()
    {
        return [
            ['collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection'],
        ];
    }
}
