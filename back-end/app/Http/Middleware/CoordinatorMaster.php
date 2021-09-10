<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
class CoordinatorMaster
{
    public function handle($request, Closure $next)
    {
        if (Auth::guard(COORDINATOR_GUARD)->check() && Auth::guard(COORDINATOR_GUARD)->user()->type == COORDINATOR_LEVEL['MASTER'])
            return $next($request);
        else
            return route('guest.login');
    }
}
