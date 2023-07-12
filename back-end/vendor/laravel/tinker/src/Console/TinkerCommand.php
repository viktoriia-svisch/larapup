<?php
namespace Laravel\Tinker\Console;
use Psy\Shell;
use Psy\Configuration;
use Illuminate\Console\Command;
use Laravel\Tinker\ClassAliasAutoloader;
use Symfony\Component\Console\Input\InputArgument;
class TinkerCommand extends Command
{
    protected $commandWhitelist = [
        'clear-compiled', 'down', 'env', 'inspire', 'migrate', 'optimize', 'up',
    ];
    protected $name = 'tinker';
    protected $description = 'Interact with your application';
    public function handle()
    {
        $this->getApplication()->setCatchExceptions(false);
        $config = new Configuration([
            'updateCheck' => 'never'
        ]);
        $config->getPresenter()->addCasters(
            $this->getCasters()
        );
        $shell = new Shell($config);
        $shell->addCommands($this->getCommands());
        $shell->setIncludes($this->argument('include'));
        $path = $this->getLaravel()->basePath().DIRECTORY_SEPARATOR.'vendor/composer/autoload_classmap.php';
        $loader = ClassAliasAutoloader::register($shell, $path);
        try {
            $shell->run();
        } finally {
            $loader->unregister();
        }
    }
    protected function getCommands()
    {
        $commands = [];
        foreach ($this->getApplication()->all() as $name => $command) {
            if (in_array($name, $this->commandWhitelist)) {
                $commands[] = $command;
            }
        }
        foreach (config('tinker.commands', []) as $command) {
            $commands[] = $this->getApplication()->resolve($command);
        }
        return $commands;
    }
    protected function getCasters()
    {
        $casters = [
            'Illuminate\Support\Collection' => 'Laravel\Tinker\TinkerCaster::castCollection',
        ];
        if (class_exists('Illuminate\Database\Eloquent\Model')) {
            $casters['Illuminate\Database\Eloquent\Model'] = 'Laravel\Tinker\TinkerCaster::castModel';
        }
        if (class_exists('Illuminate\Foundation\Application')) {
            $casters['Illuminate\Foundation\Application'] = 'Laravel\Tinker\TinkerCaster::castApplication';
        }
        return $casters;
    }
    protected function getArguments()
    {
        return [
            ['include', InputArgument::IS_ARRAY, 'Include file(s) before starting tinker'],
        ];
    }
}
