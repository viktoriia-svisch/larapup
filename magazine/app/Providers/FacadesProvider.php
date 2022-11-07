<?php
namespace App\Providers;
use App\Helpers\DateTimeHelper;
use App\Helpers\StorageHelper;
use Illuminate\Support\ServiceProvider;
class FacadesProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('datetime_helper', function () {
            return new DateTimeHelper();
        });
        $this->app->singleton('storage_helper', function () {
            return new StorageHelper();
        });
    }
    public function boot()
    {
    }
}
