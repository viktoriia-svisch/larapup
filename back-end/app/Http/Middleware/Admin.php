<?php
namespace App\Http\Middleware;
use Closure;
class Admin
{
    public function handle($request, Closure $next)
    {
dd($request);
        return $next($request);
    }
}
