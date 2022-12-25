<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class JobMakeCommand extends GeneratorCommand
{
    protected $name = 'make:job';
    protected $description = 'Create a new job class';
    protected $type = 'Job';
    protected function getStub()
    {
        return $this->option('sync')
                        ? __DIR__.'/stubs/job.stub'
                        : __DIR__.'/stubs/job-queued.stub';
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Jobs';
    }
    protected function getOptions()
    {
        return [
            ['sync', null, InputOption::VALUE_NONE, 'Indicates that job should be synchronous'],
        ];
    }
}
