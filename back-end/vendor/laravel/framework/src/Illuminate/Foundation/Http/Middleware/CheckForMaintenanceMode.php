<?php
namespace Illuminate\Foundation\Http\Middleware;
use Closure;
use Symfony\Component\HttpFoundation\IpUtils;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
class CheckForMaintenanceMode
{
    protected $app;
    protected $except = [];
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    public function handle($request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            $data = json_decode(file_get_contents($this->app->storagePath().'/framework/down'), true);
            if (isset($data['allowed']) && IpUtils::checkIp($request->ip(), (array) $data['allowed'])) {
                return $next($request);
            }
            if ($this->inExceptArray($request)) {
                return $next($request);
            }
            throw new MaintenanceModeException($data['time'], $data['retry'], $data['message']);
        }
        return $next($request);
    }
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }
            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }
        return false;
    }
}
