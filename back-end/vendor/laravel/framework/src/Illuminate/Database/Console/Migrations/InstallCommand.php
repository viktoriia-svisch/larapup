<?php
namespace Illuminate\Database\Console\Migrations;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
class InstallCommand extends Command
{
    protected $name = 'migrate:install';
    protected $description = 'Create the migration repository';
    protected $repository;
    public function __construct(MigrationRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }
    public function handle()
    {
        $this->repository->setSource($this->input->getOption('database'));
        $this->repository->createRepository();
        $this->info('Migration table created successfully.');
    }
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
        ];
    }
}
