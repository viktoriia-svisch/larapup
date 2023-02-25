<?php
namespace Illuminate\Database\Console\Seeds;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\GeneratorCommand;
class SeederMakeCommand extends GeneratorCommand
{
    protected $name = 'make:seeder';
    protected $description = 'Create a new seeder class';
    protected $type = 'Seeder';
    protected $composer;
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct($files);
        $this->composer = $composer;
    }
    public function handle()
    {
        parent::handle();
        $this->composer->dumpAutoloads();
    }
    protected function getStub()
    {
        return __DIR__.'/stubs/seeder.stub';
    }
    protected function getPath($name)
    {
        return $this->laravel->databasePath().'/seeds/'.$name.'.php';
    }
    protected function qualifyClass($name)
    {
        return $name;
    }
}
