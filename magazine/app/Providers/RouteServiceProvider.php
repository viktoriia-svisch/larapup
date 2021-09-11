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
        $this->mapApiRoutes();
        $this->mapAdminRoutes();
        $this->mapCoordinatorRoutes();
        $this->mapStudentRoutes();
        $this->mapWebRoutes();
    }
    protected function mapStudentRoutes()
    {
        Route::middleware(['web'])
            ->prefix('/student')
            ->namespace($this->namespace . '\Student')
            ->group(base_path('routes/student.php'));
    }
    protected function mapAdminRoutes()
    {
        Route::middleware(['web'])
            ->prefix('admin')
            ->namespace($this->namespace . '\Admin')
            ->group(base_path('routes/admin.php'));
    }
    protected function mapCoordinatorRoutes()
    {
        Route::middleware(['web'])
            ->prefix('coordinator')
            ->namespace($this->namespace . '\Coordinator')
            ->group(base_path('routes/coordinator.php'));
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
}
