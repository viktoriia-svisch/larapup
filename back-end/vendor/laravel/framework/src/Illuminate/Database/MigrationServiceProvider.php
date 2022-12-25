<?php
namespace Illuminate\Database;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
class MigrationServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register()
    {
        $this->registerRepository();
        $this->registerMigrator();
        $this->registerCreator();
    }
    protected function registerRepository()
    {
        $this->app->singleton('migration.repository', function ($app) {
            $table = $app['config']['database.migrations'];
            return new DatabaseMigrationRepository($app['db'], $table);
        });
    }
    protected function registerMigrator()
    {
        $this->app->singleton('migrator', function ($app) {
            $repository = $app['migration.repository'];
            return new Migrator($repository, $app['db'], $app['files']);
        });
    }
    protected function registerCreator()
    {
        $this->app->singleton('migration.creator', function ($app) {
            return new MigrationCreator($app['files']);
        });
    }
    public function provides()
    {
        return [
            'migrator', 'migration.repository', 'migration.creator',
        ];
    }
}
