<?php
namespace Illuminate\Database\Console\Seeds;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
class SeedCommand extends Command
{
    use ConfirmableTrait;
    protected $name = 'db:seed';
    protected $description = 'Seed the database with records';
    protected $resolver;
    public function __construct(Resolver $resolver)
    {
        parent::__construct();
        $this->resolver = $resolver;
    }
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }
        $this->resolver->setDefaultConnection($this->getDatabase());
        Model::unguarded(function () {
            $this->getSeeder()->__invoke();
        });
        $this->info('Database seeding completed successfully.');
    }
    protected function getSeeder()
    {
        $class = $this->laravel->make($this->input->getOption('class'));
        return $class->setContainer($this->laravel)->setCommand($this);
    }
    protected function getDatabase()
    {
        $database = $this->input->getOption('database');
        return $database ?: $this->laravel['config']['database.default'];
    }
    protected function getOptions()
    {
        return [
            ['class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', 'DatabaseSeeder'],
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
        ];
    }
}
