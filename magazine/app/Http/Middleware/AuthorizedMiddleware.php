<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthorizedMiddleware
{
    public function handle($request, Closure $next)
    {
        $arrGuard = [
            STUDENT_GUARD,
            COORDINATOR_GUARD,
            ADMIN_GUARD,
            GUEST_GUARD
        ];
        foreach ($arrGuard as $guard) {
            if (Auth::guard($guard)->check())
                return $next($request);
        }
        return redirect()->route("student.login");
    }
}
