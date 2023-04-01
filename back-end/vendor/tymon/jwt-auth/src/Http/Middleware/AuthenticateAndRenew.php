<?php
namespace Tymon\JWTAuth\Http\Middleware;
use Closure;
class AuthenticateAndRenew extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        $this->authenticate($request);
        $response = $next($request);
        return $this->setAuthenticationHeader($response);
    }
}
