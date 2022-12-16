<?php
namespace Illuminate\Database\Console\Migrations;
use Illuminate\Console\Command;
class BaseCommand extends Command
{
    protected function getMigrationPaths()
    {
        if ($this->input->hasOption('path') && $this->option('path')) {
            return collect($this->option('path'))->map(function ($path) {
                return ! $this->usingRealPath()
                                ? $this->laravel->basePath().'/'.$path
                                : $path;
            })->all();
        }
        return array_merge(
            $this->migrator->paths(), [$this->getMigrationPath()]
        );
    }
    protected function usingRealPath()
    {
        return $this->input->hasOption('realpath') && $this->option('realpath');
    }
    protected function getMigrationPath()
    {
        return $this->laravel->databasePath().DIRECTORY_SEPARATOR.'migrations';
    }
}
