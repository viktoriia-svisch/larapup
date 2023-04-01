<?php
namespace Illuminate\Database\Console\Migrations;
use Illuminate\Support\Collection;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;
class StatusCommand extends BaseCommand
{
    protected $name = 'migrate:status';
    protected $description = 'Show the status of each migration';
    protected $migrator;
    public function __construct(Migrator $migrator)
    {
        parent::__construct();
        $this->migrator = $migrator;
    }
    public function handle()
    {
        $this->migrator->setConnection($this->option('database'));
        if (! $this->migrator->repositoryExists()) {
            return $this->error('Migration table not found.');
        }
        $ran = $this->migrator->getRepository()->getRan();
        $batches = $this->migrator->getRepository()->getMigrationBatches();
        if (count($migrations = $this->getStatusFor($ran, $batches)) > 0) {
            $this->table(['Ran?', 'Migration', 'Batch'], $migrations);
        } else {
            $this->error('No migrations found');
        }
    }
    protected function getStatusFor(array $ran, array $batches)
    {
        return Collection::make($this->getAllMigrationFiles())
                    ->map(function ($migration) use ($ran, $batches) {
                        $migrationName = $this->migrator->getMigrationName($migration);
                        return in_array($migrationName, $ran)
                                ? ['<info>Yes</info>', $migrationName, $batches[$migrationName]]
                                : ['<fg=red>No</fg=red>', $migrationName];
                    });
    }
    protected function getAllMigrationFiles()
    {
        return $this->migrator->getMigrationFiles($this->getMigrationPaths());
    }
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files to use'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
        ];
    }
}
