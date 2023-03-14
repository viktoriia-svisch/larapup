<?php
namespace Illuminate\Foundation\Bootstrap;
use Exception;
use SplFileInfo;
use Illuminate\Config\Repository;
use Symfony\Component\Finder\Finder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
class LoadConfiguration
{
    public function bootstrap(Application $app)
    {
        $items = [];
        if (file_exists($cached = $app->getCachedConfigPath())) {
            $items = require $cached;
            $loadedFromCache = true;
        }
        $app->instance('config', $config = new Repository($items));
        if (! isset($loadedFromCache)) {
            $this->loadConfigurationFiles($app, $config);
        }
        $app->detectEnvironment(function () use ($config) {
            return $config->get('app.env', 'production');
        });
        date_default_timezone_set($config->get('app.timezone', 'UTC'));
        mb_internal_encoding('UTF-8');
    }
    protected function loadConfigurationFiles(Application $app, RepositoryContract $repository)
    {
        $files = $this->getConfigurationFiles($app);
        if (! isset($files['app'])) {
            throw new Exception('Unable to load the "app" configuration file.');
        }
        foreach ($files as $key => $path) {
            $repository->set($key, require $path);
        }
    }
    protected function getConfigurationFiles(Application $app)
    {
        $files = [];
        $configPath = realpath($app->configPath());
        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);
            $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }
        ksort($files, SORT_NATURAL);
        return $files;
    }
    protected function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();
        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }
        return $nested;
    }
}
