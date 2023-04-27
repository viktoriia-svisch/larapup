<?php
namespace Illuminate\Foundation\Console;
use Throwable;
use LogicException;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
class ConfigCacheCommand extends Command
{
    protected $name = 'config:cache';
    protected $description = 'Create a cache file for faster configuration loading';
    protected $files;
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }
    public function handle()
    {
        $this->call('config:clear');
        $config = $this->getFreshConfiguration();
        $configPath = $this->laravel->getCachedConfigPath();
        $this->files->put(
            $configPath, '<?php return '.var_export($config, true).';'.PHP_EOL
        );
        try {
            require $configPath;
        } catch (Throwable $e) {
            $this->files->delete($configPath);
            throw new LogicException('Your configuration files are not serializable.', 0, $e);
        }
        $this->info('Configuration cached successfully!');
    }
    protected function getFreshConfiguration()
    {
        $app = require $this->laravel->bootstrapPath().'/app.php';
        $app->make(ConsoleKernelContract::class)->bootstrap();
        return $app['config']->all();
    }
}
