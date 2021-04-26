<?php
namespace App\Http\Middleware;
use Closure;
class Admin
{
    public function handle($request, Closure $next)
    {
        if(Auth::guard(ADMIN_GUARD)->check()){
            return redirect('/home');
        }
        return $next($request);
    }
}
