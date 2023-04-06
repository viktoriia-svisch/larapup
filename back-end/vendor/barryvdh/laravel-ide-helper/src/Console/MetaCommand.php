<?php
namespace Barryvdh\LaravelIdeHelper\Console;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class MetaCommand extends Command
{
    protected $name = 'ide-helper:meta';
    protected $description = 'Generate metadata for PhpStorm';
    protected $files;
    protected $view;
    protected $config;
    protected $methods = [
      'new \Illuminate\Contracts\Container\Container',
      '\Illuminate\Contracts\Container\Container::make(0)',
      '\Illuminate\Contracts\Container\Container::makeWith(0)',
      '\App::make(0)',
      '\App::makeWith(0)',
      '\app(0)',
      '\resolve(0)',
    ];
    public function __construct($files, $view, $config)
    {
        $this->files = $files;
        $this->view = $view;
        $this->config = $config;
        parent::__construct();
    }
    public function handle()
    {
        $this->registerClassAutoloadExceptions();
        $bindings = array();
        foreach ($this->getAbstracts() as $abstract) {
            if (in_array($abstract, ['validator', 'seeder'])) {
                continue;
            }
            try {
                $concrete = $this->laravel->make($abstract);
                if (is_object($concrete)) {
                    $bindings[$abstract] = get_class($concrete);
                }
            } catch (\Exception $e) {
                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->comment("Cannot make '$abstract': ".$e->getMessage());
                }
            }
        }
        $content = $this->view->make('meta', [
          'bindings' => $bindings,
          'methods' => $this->methods,
        ])->render();
        $filename = $this->option('filename');
        $written = $this->files->put($filename, $content);
        if ($written !== false) {
            $this->info("A new meta file was written to $filename");
        } else {
            $this->error("The meta file could not be created at $filename");
        }
    }
    protected function getAbstracts()
    {
        $abstracts = $this->laravel->getBindings();
        $keys = array_keys($abstracts);
        sort($keys);
        return $keys;
    }
    protected function registerClassAutoloadExceptions()
    {
        spl_autoload_register(function ($class) {
            throw new \ReflectionException("Class '$class' not found.");
        });
    }
    protected function getOptions()
    {
        $filename = $this->config->get('ide-helper.meta_filename');
        return array(
            array('filename', 'F', InputOption::VALUE_OPTIONAL, 'The path to the meta file', $filename),
        );
    }
}
