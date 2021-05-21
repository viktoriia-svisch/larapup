<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
class RedirectIfNotAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            switch ($guard) {
                case ADMIN_GUARD:
                    return redirect('admin/dashboard');
                    break;
                case STUDENT_GUARD:
                    return redirect('student/dashboard');
                    break;
                case COORDINATOR_GUARD:
                    return redirect('coordinator/dashboard');
                    break;
            }
            return redirect('guest/article');
        }
        return $next($request);
    }
}
