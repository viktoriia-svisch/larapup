<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
class RedirectIfNotAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return $next($request);
        }
        switch ($guard){
            case ADMIN_GUARD:
                return redirect('admin/login');
                break;
            case STUDENT_GUARD:
                return redirect('/student/login');
                break;
            case COORDINATOR_GUARD:
                if(Auth::guard(COORDINATOR_GUARD)->user()->level == COORDINATOR_LEVEL['NORMAL']){
                    return redirect('/coordinator/login'); 
                }
                else{
                    return redirect('/coordinator/login'); 
                }
                break;
        }
        return redirect('/guest/login');
    }
}
