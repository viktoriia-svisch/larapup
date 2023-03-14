<?php
namespace Illuminate\Pagination;
use Illuminate\Support\ServiceProvider;
class PaginationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'pagination');
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/resources/views' => $this->app->resourcePath('views/vendor/pagination'),
            ], 'laravel-pagination');
        }
    }
    public function register()
    {
        Paginator::viewFactoryResolver(function () {
            return $this->app['view'];
        });
        Paginator::currentPathResolver(function () {
            return $this->app['request']->url();
        });
        Paginator::currentPageResolver(function ($pageName = 'page') {
            $page = $this->app['request']->input($pageName);
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }
            return 1;
        });
    }
}
