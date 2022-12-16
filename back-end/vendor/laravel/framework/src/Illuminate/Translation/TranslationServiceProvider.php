<?php
namespace Illuminate\Translation;
use Illuminate\Support\ServiceProvider;
class TranslationServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register()
    {
        $this->registerLoader();
        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];
            $locale = $app['config']['app.locale'];
            $trans = new Translator($loader, $locale);
            $trans->setFallback($app['config']['app.fallback_locale']);
            return $trans;
        });
    }
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new FileLoader($app['files'], $app['path.lang']);
        });
    }
    public function provides()
    {
        return ['translator', 'translation.loader'];
    }
}
