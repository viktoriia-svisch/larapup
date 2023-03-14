<?php
namespace Illuminate\Http\Middleware;
use Closure;
use Symfony\Component\HttpFoundation\Response;
class CheckResponseForModifications
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if ($response instanceof Response) {
            $response->isNotModified($request);
        }
        return $response;
    }
}
