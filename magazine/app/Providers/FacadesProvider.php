<?php
namespace App\Providers;
use App\Helpers\DateTimeHelper;
use Illuminate\Support\ServiceProvider;
class FacadesProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('datetime_helper', function () {
            return new DateTimeHelper();
        });
    }
    public function boot()
    {
    }
}
