<?php
namespace Illuminate\Foundation\Http\Middleware;
use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Support\InteractsWithTime;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Cookie\Middleware\EncryptCookies;
class VerifyCsrfToken
{
    use InteractsWithTime;
    protected $app;
    protected $encrypter;
    protected $except = [];
    protected $addHttpCookie = true;
    public function __construct(Application $app, Encrypter $encrypter)
    {
        $this->app = $app;
        $this->encrypter = $encrypter;
    }
    public function handle($request, Closure $next)
    {
        if (
            $this->isReading($request) ||
            $this->runningUnitTests() ||
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)
        ) {
            return tap($next($request), function ($response) use ($request) {
                if ($this->shouldAddXsrfTokenCookie()) {
                    $this->addCookieToResponse($request, $response);
                }
            });
        }
        throw new TokenMismatchException;
    }
    protected function isReading($request)
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }
    protected function runningUnitTests()
    {
        return $this->app->runningInConsole() && $this->app->runningUnitTests();
    }
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }
            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }
        return false;
    }
    protected function tokensMatch($request)
    {
        $token = $this->getTokenFromRequest($request);
        return is_string($request->session()->token()) &&
               is_string($token) &&
               hash_equals($request->session()->token(), $token);
    }
    protected function getTokenFromRequest($request)
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
        if (! $token && $header = $request->header('X-XSRF-TOKEN')) {
            $token = $this->encrypter->decrypt($header, static::serialized());
        }
        return $token;
    }
    public function shouldAddXsrfTokenCookie()
    {
        return $this->addHttpCookie;
    }
    protected function addCookieToResponse($request, $response)
    {
        $config = config('session');
        $response->headers->setCookie(
            new Cookie(
                'XSRF-TOKEN', $request->session()->token(), $this->availableAt(60 * $config['lifetime']),
                $config['path'], $config['domain'], $config['secure'], false, false, $config['same_site'] ?? null
            )
        );
        return $response;
    }
    public static function serialized()
    {
        return EncryptCookies::serialized('XSRF-TOKEN');
    }
}
