<?php
namespace Illuminate\Auth\Middleware;
use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
class AuthenticateWithBasicAuth
{
    protected $auth;
    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
    }
    public function handle($request, Closure $next, $guard = null, $field = null)
    {
        $this->auth->guard($guard)->basic($field ?: 'email');
        return $next($request);
    }
}
