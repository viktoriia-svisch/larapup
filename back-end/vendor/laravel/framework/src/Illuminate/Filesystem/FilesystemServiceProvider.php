<?php
namespace Illuminate\Filesystem;
use Illuminate\Support\ServiceProvider;
class FilesystemServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerNativeFilesystem();
        $this->registerFlysystem();
    }
    protected function registerNativeFilesystem()
    {
        $this->app->singleton('files', function () {
            return new Filesystem;
        });
    }
    protected function registerFlysystem()
    {
        $this->registerManager();
        $this->app->singleton('filesystem.disk', function () {
            return $this->app['filesystem']->disk($this->getDefaultDriver());
        });
        $this->app->singleton('filesystem.cloud', function () {
            return $this->app['filesystem']->disk($this->getCloudDriver());
        });
    }
    protected function registerManager()
    {
        $this->app->singleton('filesystem', function () {
            return new FilesystemManager($this->app);
        });
    }
    protected function getDefaultDriver()
    {
        return $this->app['config']['filesystems.default'];
    }
    protected function getCloudDriver()
    {
        return $this->app['config']['filesystems.cloud'];
    }
}
