<?php
namespace Illuminate\Queue\Console;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;
class TableCommand extends Command
{
    protected $name = 'queue:table';
    protected $description = 'Create a migration for the queue jobs database table';
    protected $files;
    protected $composer;
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();
        $this->files = $files;
        $this->composer = $composer;
    }
    public function handle()
    {
        $table = $this->laravel['config']['queue.connections.database.table'];
        $this->replaceMigration(
            $this->createBaseMigration($table), $table, Str::studly($table)
        );
        $this->info('Migration created successfully!');
        $this->composer->dumpAutoloads();
    }
    protected function createBaseMigration($table = 'jobs')
    {
        return $this->laravel['migration.creator']->create(
            'create_'.$table.'_table', $this->laravel->databasePath().'/migrations'
        );
    }
    protected function replaceMigration($path, $table, $tableClassName)
    {
        $stub = str_replace(
            ['{{table}}', '{{tableClassName}}'],
            [$table, $tableClassName],
            $this->files->get(__DIR__.'/stubs/jobs.stub')
        );
        $this->files->put($path, $stub);
    }
}
