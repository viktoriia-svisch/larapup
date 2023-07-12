<?php
namespace Illuminate\Cookie\Middleware;
use Closure;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
class AddQueuedCookiesToResponse
{
    protected $cookies;
    public function __construct(CookieJar $cookies)
    {
        $this->cookies = $cookies;
    }
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        foreach ($this->cookies->getQueuedCookies() as $cookie) {
            $response->headers->setCookie($cookie);
        }
        return $response;
    }
}
