<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            switch ($guard){
                case ADMIN_GUARD:
                    return redirect('/AdminHome');
                    break;
                case STUDENT_GUARD:
                    return redirect('/home');
                    break;
                case COORDINATOR_GUARD:
                    if(Auth::guard(COORDINATOR_GUARD)->user()->level == COORDINATOR_LEVEL['NORMAL']){
                        return redirect('/NCoordinatorHome'); 
                    }
                    else{
                        return redirect('/MCoordinatorHome'); 
                    }
                    break;
                default:
                    return $next($request);
                    break;
            }
        }
        return $next($request);
    }
}
