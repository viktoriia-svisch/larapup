<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            $redirect = route('guest.dashboard');
            switch ($guard) {
                case STUDENT_GUARD:
                    $redirect = route('student.dashboard');
                    break;
                case COORDINATOR_GUARD:
                    $redirect = route('coordinator.dashboard');
                    break;
                case ADMIN_GUARD:
                    $redirect = route('admin.dashboard');
                    break;
            }
            return redirect($redirect);
        }
        return $next($request);
    }
}
