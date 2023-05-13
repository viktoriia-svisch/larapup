<?php
namespace Illuminate\Database\Migrations;
use Closure;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Filesystem\Filesystem;
class MigrationCreator
{
    protected $files;
    protected $postCreate = [];
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }
    public function create($name, $path, $table = null, $create = false)
    {
        $this->ensureMigrationDoesntAlreadyExist($name);
        $stub = $this->getStub($table, $create);
        $this->files->put(
            $path = $this->getPath($name, $path),
            $this->populateStub($name, $stub, $table)
        );
        $this->firePostCreateHooks($table);
        return $path;
    }
    protected function ensureMigrationDoesntAlreadyExist($name)
    {
        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }
    protected function getStub($table, $create)
    {
        if (is_null($table)) {
            return $this->files->get($this->stubPath().'/blank.stub');
        }
        $stub = $create ? 'create.stub' : 'update.stub';
        return $this->files->get($this->stubPath()."/{$stub}");
    }
    protected function populateStub($name, $stub, $table)
    {
        $stub = str_replace('DummyClass', $this->getClassName($name), $stub);
        if (! is_null($table)) {
            $stub = str_replace('DummyTable', $table, $stub);
        }
        return $stub;
    }
    protected function getClassName($name)
    {
        return Str::studly($name);
    }
    protected function getPath($name, $path)
    {
        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
    }
    protected function firePostCreateHooks($table)
    {
        foreach ($this->postCreate as $callback) {
            call_user_func($callback, $table);
        }
    }
    public function afterCreate(Closure $callback)
    {
        $this->postCreate[] = $callback;
    }
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }
    public function stubPath()
    {
        return __DIR__.'/stubs';
    }
    public function getFilesystem()
    {
        return $this->files;
    }
}
