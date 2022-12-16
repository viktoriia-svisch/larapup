<?php
namespace Illuminate\Validation;
use Illuminate\Support\ServiceProvider;
class ValidationServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register()
    {
        $this->registerPresenceVerifier();
        $this->registerValidationFactory();
    }
    protected function registerValidationFactory()
    {
        $this->app->singleton('validator', function ($app) {
            $validator = new Factory($app['translator'], $app);
            if (isset($app['db'], $app['validation.presence'])) {
                $validator->setPresenceVerifier($app['validation.presence']);
            }
            return $validator;
        });
    }
    protected function registerPresenceVerifier()
    {
        $this->app->singleton('validation.presence', function ($app) {
            return new DatabasePresenceVerifier($app['db']);
        });
    }
    public function provides()
    {
        return [
            'validator', 'validation.presence',
        ];
    }
}
