<?php
namespace Barryvdh\LaravelIdeHelper;
use Barryvdh\LaravelIdeHelper\Console\EloquentCommand;
use Barryvdh\LaravelIdeHelper\Console\GeneratorCommand;
use Barryvdh\LaravelIdeHelper\Console\MetaCommand;
use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
class IdeHelperServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function boot()
    {
        if ($this->app->has('view')) {
            $viewPath = __DIR__ . '/../resources/views';
            $this->loadViewsFrom($viewPath, 'ide-helper');
        }
        $configPath = __DIR__ . '/../config/ide-helper.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('ide-helper.php');
        } else {
            $publishPath = base_path('config/ide-helper.php');
        }
        $this->publishes([$configPath => $publishPath], 'config');
    }
    public function register()
    {
        $configPath = __DIR__ . '/../config/ide-helper.php';
        $this->mergeConfigFrom($configPath, 'ide-helper');
        $localViewFactory = $this->createLocalViewFactory();
        $this->app->singleton(
            'command.ide-helper.generate',
            function ($app) use ($localViewFactory) {
                return new GeneratorCommand($app['config'], $app['files'], $localViewFactory);
            }
        );
        $this->app->singleton(
            'command.ide-helper.models',
            function ($app) {
                return new ModelsCommand($app['files']);
            }
        );
        $this->app->singleton(
            'command.ide-helper.meta',
            function ($app) use ($localViewFactory) {
                return new MetaCommand($app['files'], $localViewFactory, $app['config']);
            }
        );
        $this->app->singleton(
            'command.ide-helper.eloquent',
            function ($app) use ($localViewFactory) {
                return new EloquentCommand($app['files']);
            }
        );
        $this->commands(
            'command.ide-helper.generate',
            'command.ide-helper.models',
            'command.ide-helper.meta',
            'command.ide-helper.eloquent'
        );
    }
    public function provides()
    {
        return array('command.ide-helper.generate', 'command.ide-helper.models');
    }
    private function createLocalViewFactory()
    {
        $resolver = new EngineResolver();
        $resolver->register('php', function () {
            return new PhpEngine();
        });
        $finder = new FileViewFinder($this->app['files'], [__DIR__ . '/../resources/views']);
        $factory = new Factory($resolver, $finder, $this->app['events']);
        $factory->addExtension('php', 'php');
        return $factory;
    }
}
