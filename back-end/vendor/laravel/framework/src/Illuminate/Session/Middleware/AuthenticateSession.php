<?php
namespace Illuminate\Session\Middleware;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
class AuthenticateSession
{
    protected $auth;
    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
    }
    public function handle($request, Closure $next)
    {
        if (! $request->user() || ! $request->session()) {
            return $next($request);
        }
        if ($this->auth->viaRemember()) {
            $passwordHash = explode('|', $request->cookies->get($this->auth->getRecallerName()))[2];
            if ($passwordHash != $request->user()->getAuthPassword()) {
                $this->logout($request);
            }
        }
        if (! $request->session()->has('password_hash')) {
            $this->storePasswordHashInSession($request);
        }
        if ($request->session()->get('password_hash') !== $request->user()->getAuthPassword()) {
            $this->logout($request);
        }
        return tap($next($request), function () use ($request) {
            $this->storePasswordHashInSession($request);
        });
    }
    protected function storePasswordHashInSession($request)
    {
        if (! $request->user()) {
            return;
        }
        $request->session()->put([
            'password_hash' => $request->user()->getAuthPassword(),
        ]);
    }
    protected function logout($request)
    {
        $this->auth->logout();
        $request->session()->flush();
        throw new AuthenticationException;
    }
}
