<?php
namespace App\Http\Middleware;
use Closure;
class Student
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
