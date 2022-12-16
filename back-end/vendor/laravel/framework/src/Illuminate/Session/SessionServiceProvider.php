<?php
namespace Illuminate\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\Session\Middleware\StartSession;
class SessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerSessionManager();
        $this->registerSessionDriver();
        $this->app->singleton(StartSession::class);
    }
    protected function registerSessionManager()
    {
        $this->app->singleton('session', function ($app) {
            return new SessionManager($app);
        });
    }
    protected function registerSessionDriver()
    {
        $this->app->singleton('session.store', function ($app) {
            return $app->make('session')->driver();
        });
    }
}
