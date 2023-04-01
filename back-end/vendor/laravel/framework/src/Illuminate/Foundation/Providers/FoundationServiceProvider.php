<?php
namespace Illuminate\Foundation\Providers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\AggregateServiceProvider;
class FoundationServiceProvider extends AggregateServiceProvider
{
    protected $providers = [
        FormRequestServiceProvider::class,
    ];
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Exceptions/views' => $this->app->resourcePath('views/errors/'),
            ], 'laravel-errors');
        }
    }
    public function register()
    {
        parent::register();
        $this->registerRequestValidation();
        $this->registerRequestSignatureValidation();
    }
    public function registerRequestValidation()
    {
        Request::macro('validate', function (array $rules, ...$params) {
            return validator()->validate($this->all(), $rules, ...$params);
        });
    }
    public function registerRequestSignatureValidation()
    {
        Request::macro('hasValidSignature', function ($absolute = true) {
            return URL::hasValidSignature($this, $absolute);
        });
    }
}
