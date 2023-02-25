<?php
namespace Illuminate\Cookie;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\InteractsWithTime;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Contracts\Cookie\QueueingFactory as JarContract;
class CookieJar implements JarContract
{
    use InteractsWithTime, Macroable;
    protected $path = '/';
    protected $domain;
    protected $secure = false;
    protected $sameSite;
    protected $queued = [];
    public function make($name, $value, $minutes = 0, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
    {
        [$path, $domain, $secure, $sameSite] = $this->getPathAndDomain($path, $domain, $secure, $sameSite);
        $time = ($minutes == 0) ? 0 : $this->availableAt($minutes * 60);
        return new Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
    public function forever($name, $value, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
    {
        return $this->make($name, $value, 2628000, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
    public function forget($name, $path = null, $domain = null)
    {
        return $this->make($name, null, -2628000, $path, $domain);
    }
    public function hasQueued($key)
    {
        return ! is_null($this->queued($key));
    }
    public function queued($key, $default = null)
    {
        return Arr::get($this->queued, $key, $default);
    }
    public function queue(...$parameters)
    {
        if (head($parameters) instanceof Cookie) {
            $cookie = head($parameters);
        } else {
            $cookie = call_user_func_array([$this, 'make'], $parameters);
        }
        $this->queued[$cookie->getName()] = $cookie;
    }
    public function unqueue($name)
    {
        unset($this->queued[$name]);
    }
    protected function getPathAndDomain($path, $domain, $secure = null, $sameSite = null)
    {
        return [$path ?: $this->path, $domain ?: $this->domain, is_bool($secure) ? $secure : $this->secure, $sameSite ?: $this->sameSite];
    }
    public function setDefaultPathAndDomain($path, $domain, $secure = false, $sameSite = null)
    {
        [$this->path, $this->domain, $this->secure, $this->sameSite] = [$path, $domain, $secure, $sameSite];
        return $this;
    }
    public function getQueuedCookies()
    {
        return $this->queued;
    }
}
