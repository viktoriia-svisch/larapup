<?php
namespace Illuminate\Support;
class AggregateServiceProvider extends ServiceProvider
{
    protected $providers = [];
    protected $instances = [];
    public function register()
    {
        $this->instances = [];
        foreach ($this->providers as $provider) {
            $this->instances[] = $this->app->register($provider);
        }
    }
    public function provides()
    {
        $provides = [];
        foreach ($this->providers as $provider) {
            $instance = $this->app->resolveProvider($provider);
            $provides = array_merge($provides, $instance->provides());
        }
        return $provides;
    }
}
