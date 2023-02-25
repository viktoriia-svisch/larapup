<?php
namespace Laravel\Tinker;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Laravel\Tinker\Console\TinkerCommand;
class TinkerServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function boot()
    {
        $source = realpath($raw = __DIR__ . '/../config/tinker.php') ?: $raw;
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('tinker.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('tinker');
        }
        $this->mergeConfigFrom($source, 'tinker');
    }
    public function register()
    {
        $this->app->singleton('command.tinker', function () {
            return new TinkerCommand;
        });
        $this->commands(['command.tinker']);
    }
    public function provides()
    {
        return ['command.tinker'];
    }
}
