<?php
namespace Tymon\JWTAuth\Http\Middleware;
use Closure;
class Authenticate extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        $this->authenticate($request);
        return $next($request);
    }
}
