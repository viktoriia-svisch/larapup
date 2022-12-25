<?php
namespace Tymon\JWTAuth\Http\Middleware;
use Closure;
use Exception;
class Check extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        if ($this->auth->parser()->setRequest($request)->hasToken()) {
            try {
                $this->auth->parseToken()->authenticate();
            } catch (Exception $e) {
            }
        }
        return $next($request);
    }
}
