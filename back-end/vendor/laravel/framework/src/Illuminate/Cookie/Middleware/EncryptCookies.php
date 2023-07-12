<?php
namespace Illuminate\Cookie\Middleware;
use Closure;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
class EncryptCookies
{
    protected $encrypter;
    protected $except = [];
    protected static $serialize = false;
    public function __construct(EncrypterContract $encrypter)
    {
        $this->encrypter = $encrypter;
    }
    public function disableFor($name)
    {
        $this->except = array_merge($this->except, (array) $name);
    }
    public function handle($request, Closure $next)
    {
        return $this->encrypt($next($this->decrypt($request)));
    }
    protected function decrypt(Request $request)
    {
        foreach ($request->cookies as $key => $cookie) {
            if ($this->isDisabled($key)) {
                continue;
            }
            try {
                $request->cookies->set($key, $this->decryptCookie($key, $cookie));
            } catch (DecryptException $e) {
                $request->cookies->set($key, null);
            }
        }
        return $request;
    }
    protected function decryptCookie($name, $cookie)
    {
        return is_array($cookie)
                        ? $this->decryptArray($cookie)
                        : $this->encrypter->decrypt($cookie, static::serialized($name));
    }
    protected function decryptArray(array $cookie)
    {
        $decrypted = [];
        foreach ($cookie as $key => $value) {
            if (is_string($value)) {
                $decrypted[$key] = $this->encrypter->decrypt($value, static::serialized($key));
            }
        }
        return $decrypted;
    }
    protected function encrypt(Response $response)
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($this->isDisabled($cookie->getName())) {
                continue;
            }
            $response->headers->setCookie($this->duplicate(
                $cookie, $this->encrypter->encrypt($cookie->getValue(), static::serialized($cookie->getName()))
            ));
        }
        return $response;
    }
    protected function duplicate(Cookie $cookie, $value)
    {
        return new Cookie(
            $cookie->getName(), $value, $cookie->getExpiresTime(),
            $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(),
            $cookie->isHttpOnly(), $cookie->isRaw(), $cookie->getSameSite()
        );
    }
    public function isDisabled($name)
    {
        return in_array($name, $this->except);
    }
    public static function serialized($name)
    {
        return static::$serialize;
    }
}
