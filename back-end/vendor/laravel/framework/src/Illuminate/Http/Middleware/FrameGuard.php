<?php
namespace Illuminate\Http\Middleware;
use Closure;
class FrameGuard
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        return $response;
    }
}
