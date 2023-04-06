<?php
namespace Barryvdh\LaravelIdeHelper\Console;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Barryvdh\LaravelIdeHelper\Generator;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
class GeneratorCommand extends Command
{
    protected $name = 'ide-helper:generate';
    protected $description = 'Generate a new IDE Helper file.';
    protected $config;
    protected $files;
    protected $view;
    protected $onlyExtend;
    public function __construct(
         $config,
        Filesystem $files,
        $view
    ) {
        $this->config = $config;
        $this->files = $files;
        $this->view = $view;
        parent::__construct();
    }
    public function handle()
    {
        if (file_exists(base_path() . '/vendor/compiled.php') ||
            file_exists(base_path() . '/bootstrap/cache/compiled.php') ||
            file_exists(base_path() . '/storage/framework/compiled.php')) {
            $this->error(
                'Error generating IDE Helper: first delete your compiled file (php artisan clear-compiled)'
            );
        } else {
            $filename = $this->argument('filename');
            $format = $this->option('format');
            if (substr($filename, -4, 4) == '.php') {
                $filename = substr($filename, 0, -4);
            }
            $filename .= '.' . $format;
            if ($this->option('memory')) {
                $this->useMemoryDriver();
            }
            $helpers = '';
            if ($this->option('helpers') || ($this->config->get('ide-helper.include_helpers'))) {
                foreach ($this->config->get('ide-helper.helper_files', array()) as $helper) {
                    if (file_exists($helper)) {
                        $helpers .= str_replace(array('<?php', '?>'), '', $this->files->get($helper));
                    }
                }
            } else {
                $helpers = '';
            }
            $generator = new Generator($this->config, $this->view, $this->getOutput(), $helpers);
            $content = $generator->generate($format);
            $written = $this->files->put($filename, $content);
            if ($written !== false) {
                $this->info("A new helper file was written to $filename");
                if ($this->option('write_mixins')) {
                    Eloquent::writeEloquentModelHelper($this, $this->files);
                }
            } else {
                $this->error("The helper file could not be created at $filename");
            }
        }
    }
    protected function useMemoryDriver()
    {
        $this->config->set(
            'database.connections.sqlite',
            array(
                'driver' => 'sqlite',
                'database' => ':memory:',
            )
        );
        $this->config->set('database.default', 'sqlite');
    }
    protected function getArguments()
    {
        $filename = $this->config->get('ide-helper.filename');
        return array(
            array(
                'filename', InputArgument::OPTIONAL, 'The path to the helper file', $filename
            ),
        );
    }
    protected function getOptions()
    {
        $format = $this->config->get('ide-helper.format');
        $writeMixins = $this->config->get('ide-helper.write_eloquent_model_mixins');
        return array(
            array('format', "F", InputOption::VALUE_OPTIONAL, 'The format for the IDE Helper', $format),
            array('write_mixins', "W", InputOption::VALUE_OPTIONAL, 'Write mixins to Laravel Model?', $writeMixins),
            array('helpers', "H", InputOption::VALUE_NONE, 'Include the helper files'),
            array('memory', "M", InputOption::VALUE_NONE, 'Use sqlite memory driver'),
            array('sublime', "S", InputOption::VALUE_NONE, 'DEPRECATED: Use different style for SublimeText CodeIntel'),
        );
    }
}
