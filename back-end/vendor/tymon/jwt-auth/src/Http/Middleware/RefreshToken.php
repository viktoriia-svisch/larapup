<?php
namespace Tymon\JWTAuth\Http\Middleware;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
class RefreshToken extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        $this->checkForToken($request);
        try {
            $token = $this->auth->parseToken()->refresh();
        } catch (JWTException $e) {
            throw new UnauthorizedHttpException('jwt-auth', $e->getMessage(), $e, $e->getCode());
        }
        $response = $next($request);
        return $this->setAuthenticationHeader($response, $token);
    }
}
