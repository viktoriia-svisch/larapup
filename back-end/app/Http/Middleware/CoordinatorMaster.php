<?php
namespace App\Http\Middleware;
use Closure;
class CoordinatorMaster
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
