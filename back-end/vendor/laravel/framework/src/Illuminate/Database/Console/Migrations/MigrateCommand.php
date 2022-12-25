<?php
namespace Illuminate\Database\Console\Migrations;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
class MigrateCommand extends BaseCommand
{
    use ConfirmableTrait;
    protected $signature = 'migrate {--database= : The database connection to use}
                {--force : Force the operation to run when in production}
                {--path= : The path to the migrations files to be executed}
                {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                {--pretend : Dump the SQL queries that would be run}
                {--seed : Indicates if the seed task should be re-run}
                {--step : Force the migrations to be run so they can be rolled back individually}';
    protected $description = 'Run the database migrations';
    protected $migrator;
    public function __construct(Migrator $migrator)
    {
        parent::__construct();
        $this->migrator = $migrator;
    }
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }
        $this->prepareDatabase();
        $this->migrator->setOutput($this->output)
                ->run($this->getMigrationPaths(), [
                    'pretend' => $this->option('pretend'),
                    'step' => $this->option('step'),
                ]);
        if ($this->option('seed') && ! $this->option('pretend')) {
            $this->call('db:seed', ['--force' => true]);
        }
    }
    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->option('database'));
        if (! $this->migrator->repositoryExists()) {
            $this->call('migrate:install', array_filter([
                '--database' => $this->option('database'),
            ]));
        }
    }
}
