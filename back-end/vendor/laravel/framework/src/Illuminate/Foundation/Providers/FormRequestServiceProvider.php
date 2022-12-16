<?php
namespace Illuminate\Foundation\Providers;
use Illuminate\Routing\Redirector;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
class FormRequestServiceProvider extends ServiceProvider
{
    public function register()
    {
    }
    public function boot()
    {
        $this->app->afterResolving(ValidatesWhenResolved::class, function ($resolved) {
            $resolved->validateResolved();
        });
        $this->app->resolving(FormRequest::class, function ($request, $app) {
            $request = FormRequest::createFrom($app['request'], $request);
            $request->setContainer($app)->setRedirector($app->make(Redirector::class));
        });
    }
}
