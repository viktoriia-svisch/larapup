<?php
namespace Symfony\Component\HttpFoundation;
class RequestMatcher implements RequestMatcherInterface
{
    private $path;
    private $host;
    private $port;
    private $methods = [];
    private $ips = [];
    private $attributes = [];
    private $schemes = [];
    public function __construct(string $path = null, string $host = null, $methods = null, $ips = null, array $attributes = [], $schemes = null, int $port = null)
    {
        $this->matchPath($path);
        $this->matchHost($host);
        $this->matchMethod($methods);
        $this->matchIps($ips);
        $this->matchScheme($schemes);
        $this->matchPort($port);
        foreach ($attributes as $k => $v) {
            $this->matchAttribute($k, $v);
        }
    }
    public function matchScheme($scheme)
    {
        $this->schemes = null !== $scheme ? array_map('strtolower', (array) $scheme) : [];
    }
    public function matchHost($regexp)
    {
        $this->host = $regexp;
    }
    public function matchPort(int $port = null)
    {
        $this->port = $port;
    }
    public function matchPath($regexp)
    {
        $this->path = $regexp;
    }
    public function matchIp($ip)
    {
        $this->matchIps($ip);
    }
    public function matchIps($ips)
    {
        $this->ips = null !== $ips ? (array) $ips : [];
    }
    public function matchMethod($method)
    {
        $this->methods = null !== $method ? array_map('strtoupper', (array) $method) : [];
    }
    public function matchAttribute($key, $regexp)
    {
        $this->attributes[$key] = $regexp;
    }
    public function matches(Request $request)
    {
        if ($this->schemes && !\in_array($request->getScheme(), $this->schemes, true)) {
            return false;
        }
        if ($this->methods && !\in_array($request->getMethod(), $this->methods, true)) {
            return false;
        }
        foreach ($this->attributes as $key => $pattern) {
            if (!preg_match('{'.$pattern.'}', $request->attributes->get($key))) {
                return false;
            }
        }
        if (null !== $this->path && !preg_match('{'.$this->path.'}', rawurldecode($request->getPathInfo()))) {
            return false;
        }
        if (null !== $this->host && !preg_match('{'.$this->host.'}i', $request->getHost())) {
            return false;
        }
        if (null !== $this->port && 0 < $this->port && $request->getPort() !== $this->port) {
            return false;
        }
        if (IpUtils::checkIp($request->getClientIp(), $this->ips)) {
            return true;
        }
        return 0 === \count($this->ips);
    }
}
