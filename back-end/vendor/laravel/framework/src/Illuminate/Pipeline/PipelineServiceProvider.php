<?php
namespace Illuminate\Pipeline;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Pipeline\Hub as PipelineHubContract;
class PipelineServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register()
    {
        $this->app->singleton(
            PipelineHubContract::class, Hub::class
        );
    }
    public function provides()
    {
        return [
            PipelineHubContract::class,
        ];
    }
}
