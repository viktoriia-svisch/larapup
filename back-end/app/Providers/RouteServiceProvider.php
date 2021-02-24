<?php
namespace App\Providers;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
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
        $this->mapApiAdminRoutes();
        $this->mapApiCoordinatorRoutes();
        $this->mapApiAdminRoutes();
    }
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }
    protected function mapApiStudentRoutes()
    {
        Route::prefix('api/student')
            ->middleware('auth.student')
            ->namespace($this->namespace . '\Student')
            ->group(base_path('routes/student.api.php'));
    }
    protected function mapApiAdminRoutes(){
        Route::prefix('api/admin')
            ->middleware('api')
            ->namespace($this->namespace. '\Admin')
            ->group(base_path('routes/admin.api.php'));
    }
    protected function mapApiAdminRoutes()
    {
        Route::middleware('auth.admin')
            ->prefix('api/admin')
            ->namespace($this->namespace . '\Admin')
            ->group(base_path('routes/admin.api.php'));
    }
    protected function mapApiCoordinatorRoutes()
    {
        Route::middleware('auth.coordinator')
            ->prefix('api/coordinator')
            ->namespace($this->namespace . '\Coordinator')
            ->group(base_path('routes/coordinator.api.php'));
    }
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
