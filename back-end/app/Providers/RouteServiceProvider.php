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
        $this->mapStudentRoutes();
        $this->mapAdminRoutes();
        $this->mapCoordinatorRoutes();
    }
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }
    protected function mapStudentRoutes()
    {
        Route::middleware(['auth.student', 'web'])
            ->prefix('/student')
            ->namespace($this->namespace . '\Student')
            ->group(base_path('routes/student.php'));
    }
    protected function mapAdminRoutes()
    {
        Route::middleware(['auth.admin', 'web'])
            ->prefix('admin')
            ->namespace($this->namespace . '\Admin')
            ->group(base_path('routes/admin.api.php'));
    }
    protected function mapCoordinatorRoutes()
    {
        Route::middleware(['auth.coordinator', 'web'])
            ->prefix('coordinator')
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
