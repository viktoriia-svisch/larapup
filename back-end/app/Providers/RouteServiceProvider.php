<?php
namespace App\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\Http\Controllers';
    public function boot()
    {
        parent::boot();
    }
    public function map()
    {
        $this->mapWebRoutes();
        $this->mapApiStudentRoutes();
        $this->mapApiCoordinatorRoutes();
        $this->mapApiAdminRoutes();
    }
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
    protected function mapApiAdminRoutes(){
        Route::prefix('api/admin')
            ->middleware('api')
            ->namespace($this->namespace. '\Admin')
            ->group(base_path('routes/admin.api.php'));
    }
    protected function mapApiStudentRoutes()
    {
        Route::prefix('api/student')
            ->middleware('api')
            ->namespace($this->namespace . '\Student')
            ->group(base_path('routes/student.api.php'));
    }
    protected function mapApiCoordinatorRoutes()
    {
        Route::middleware('api.coordinator')
            ->prefix('api/coordinator')
            ->namespace($this->namespace . '\Coordinator')
            ->group(base_path('routes/coordinator.api.php'));
    }
}
