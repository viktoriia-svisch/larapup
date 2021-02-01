<?php
namespace App\Http\Middleware;
use Closure;
class Guest
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
