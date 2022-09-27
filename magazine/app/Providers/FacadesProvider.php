<?php
namespace App\Providers;
use App\Helpers\DateTimeHelper;
use App\Helpers\StorageHelper;
use App\Helpers\UploadFileValidate;
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
        $this->app->singleton('file_validate', function () {
            return new UploadFileValidate();
        });
    }
    public function boot()
    {
    }
}
