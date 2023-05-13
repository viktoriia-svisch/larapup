<?php
namespace Illuminate\Database\Migrations;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Console\OutputStyle;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
class Migrator
{
    protected $repository;
    protected $files;
    protected $resolver;
    protected $connection;
    protected $paths = [];
    protected $output;
    public function __construct(MigrationRepositoryInterface $repository,
                                Resolver $resolver,
                                Filesystem $files)
    {
        $this->files = $files;
        $this->resolver = $resolver;
        $this->repository = $repository;
    }
    public function run($paths = [], array $options = [])
    {
        $this->notes = [];
        $files = $this->getMigrationFiles($paths);
        $this->requireFiles($migrations = $this->pendingMigrations(
            $files, $this->repository->getRan()
        ));
        $this->runPending($migrations, $options);
        return $migrations;
    }
    protected function pendingMigrations($files, $ran)
    {
        return Collection::make($files)
                ->reject(function ($file) use ($ran) {
                    return in_array($this->getMigrationName($file), $ran);
                })->values()->all();
    }
    public function runPending(array $migrations, array $options = [])
    {
        if (count($migrations) === 0) {
            $this->note('<info>Nothing to migrate.</info>');
            return;
        }
        $batch = $this->repository->getNextBatchNumber();
        $pretend = $options['pretend'] ?? false;
        $step = $options['step'] ?? false;
        foreach ($migrations as $file) {
            $this->runUp($file, $batch, $pretend);
            if ($step) {
                $batch++;
            }
        }
    }
    protected function runUp($file, $batch, $pretend)
    {
        $migration = $this->resolve(
            $name = $this->getMigrationName($file)
        );
        if ($pretend) {
            return $this->pretendToRun($migration, 'up');
        }
        $this->note("<comment>Migrating:</comment> {$name}");
        $this->runMigration($migration, 'up');
        $this->repository->log($name, $batch);
        $this->note("<info>Migrated:</info>  {$name}");
    }
    public function rollback($paths = [], array $options = [])
    {
        $this->notes = [];
        $migrations = $this->getMigrationsForRollback($options);
        if (count($migrations) === 0) {
            $this->note('<info>Nothing to rollback.</info>');
            return [];
        }
        return $this->rollbackMigrations($migrations, $paths, $options);
    }
    protected function getMigrationsForRollback(array $options)
    {
        if (($steps = $options['step'] ?? 0) > 0) {
            return $this->repository->getMigrations($steps);
        }
        return $this->repository->getLast();
    }
    protected function rollbackMigrations(array $migrations, $paths, array $options)
    {
        $rolledBack = [];
        $this->requireFiles($files = $this->getMigrationFiles($paths));
        foreach ($migrations as $migration) {
            $migration = (object) $migration;
            if (! $file = Arr::get($files, $migration->migration)) {
                $this->note("<fg=red>Migration not found:</> {$migration->migration}");
                continue;
            }
            $rolledBack[] = $file;
            $this->runDown(
                $file, $migration,
                $options['pretend'] ?? false
            );
        }
        return $rolledBack;
    }
    public function reset($paths = [], $pretend = false)
    {
        $this->notes = [];
        $migrations = array_reverse($this->repository->getRan());
        if (count($migrations) === 0) {
            $this->note('<info>Nothing to rollback.</info>');
            return [];
        }
        return $this->resetMigrations($migrations, $paths, $pretend);
    }
    protected function resetMigrations(array $migrations, array $paths, $pretend = false)
    {
        $migrations = collect($migrations)->map(function ($m) {
            return (object) ['migration' => $m];
        })->all();
        return $this->rollbackMigrations(
            $migrations, $paths, compact('pretend')
        );
    }
    protected function runDown($file, $migration, $pretend)
    {
        $instance = $this->resolve(
            $name = $this->getMigrationName($file)
        );
        $this->note("<comment>Rolling back:</comment> {$name}");
        if ($pretend) {
            return $this->pretendToRun($instance, 'down');
        }
        $this->runMigration($instance, 'down');
        $this->repository->delete($migration);
        $this->note("<info>Rolled back:</info>  {$name}");
    }
    protected function runMigration($migration, $method)
    {
        $connection = $this->resolveConnection(
            $migration->getConnection()
        );
        $callback = function () use ($migration, $method) {
            if (method_exists($migration, $method)) {
                $migration->{$method}();
            }
        };
        $this->getSchemaGrammar($connection)->supportsSchemaTransactions()
            && $migration->withinTransaction
                    ? $connection->transaction($callback)
                    : $callback();
    }
    protected function pretendToRun($migration, $method)
    {
        foreach ($this->getQueries($migration, $method) as $query) {
            $name = get_class($migration);
            $this->note("<info>{$name}:</info> {$query['query']}");
        }
    }
    protected function getQueries($migration, $method)
    {
        $db = $this->resolveConnection(
            $migration->getConnection()
        );
        return $db->pretend(function () use ($migration, $method) {
            if (method_exists($migration, $method)) {
                $migration->{$method}();
            }
        });
    }
    public function resolve($file)
    {
        $class = Str::studly(implode('_', array_slice(explode('_', $file), 4)));
        return new $class;
    }
    public function getMigrationFiles($paths)
    {
        return Collection::make($paths)->flatMap(function ($path) {
            return Str::endsWith($path, '.php') ? [$path] : $this->files->glob($path.'
    public function requireFiles(array $files)
    {
        foreach ($files as $file) {
            $this->files->requireOnce($file);
        }
    }
    public function getMigrationName($path)
    {
        return str_replace('.php', '', basename($path));
    }
    public function path($path)
    {
        $this->paths = array_unique(array_merge($this->paths, [$path]));
    }
    public function paths()
    {
        return $this->paths;
    }
    public function getConnection()
    {
        return $this->connection;
    }
    public function setConnection($name)
    {
        if (! is_null($name)) {
            $this->resolver->setDefaultConnection($name);
        }
        $this->repository->setSource($name);
        $this->connection = $name;
    }
    public function resolveConnection($connection)
    {
        return $this->resolver->connection($connection ?: $this->connection);
    }
    protected function getSchemaGrammar($connection)
    {
        if (is_null($grammar = $connection->getSchemaGrammar())) {
            $connection->useDefaultSchemaGrammar();
            $grammar = $connection->getSchemaGrammar();
        }
        return $grammar;
    }
    public function getRepository()
    {
        return $this->repository;
    }
    public function repositoryExists()
    {
        return $this->repository->repositoryExists();
    }
    public function getFilesystem()
    {
        return $this->files;
    }
    public function setOutput(OutputStyle $output)
    {
        $this->output = $output;
        return $this;
    }
    protected function note($message)
    {
        if ($this->output) {
            $this->output->writeln($message);
        }
    }
}
