<?php
namespace Illuminate\Foundation\Providers;
use Illuminate\Support\Composer;
use Illuminate\Support\ServiceProvider;
class ComposerServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register()
    {
        $this->app->singleton('composer', function ($app) {
            return new Composer($app['files'], $app->basePath());
        });
    }
    public function provides()
    {
        return ['composer'];
    }
}
