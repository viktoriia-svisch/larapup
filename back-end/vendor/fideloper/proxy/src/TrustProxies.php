<?php
namespace Fideloper\Proxy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Config\Repository;
class TrustProxies
{
    protected $config;
    protected $proxies;
    protected $headers;
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }
    public function handle(Request $request, Closure $next)
    {
        $request::setTrustedProxies([], $this->getTrustedHeaderNames()); 
        $this->setTrustedProxyIpAddresses($request);
        return $next($request);
    }
    protected function setTrustedProxyIpAddresses(Request $request)
    {
        $trustedIps = $this->proxies ?: $this->config->get('trustedproxy.proxies');
        if ($trustedIps === '*' || $trustedIps === '**') {
            return $this->setTrustedProxyIpAddressesToTheCallingIp($request);
        }
        $trustedIps = is_string($trustedIps) ? array_map('trim', explode(',', $trustedIps)) : $trustedIps;
        if (is_array($trustedIps)) {
            return $this->setTrustedProxyIpAddressesToSpecificIps($request, $trustedIps);
        }
    }
    private function setTrustedProxyIpAddressesToSpecificIps(Request $request, $trustedIps)
    {
        $request->setTrustedProxies((array) $trustedIps, $this->getTrustedHeaderNames());
    }
    private function setTrustedProxyIpAddressesToTheCallingIp(Request $request)
    {
        $request->setTrustedProxies([$request->server->get('REMOTE_ADDR')], $this->getTrustedHeaderNames());
    }
    protected function getTrustedHeaderNames()
    {
        $headers = $this->headers ?: $this->config->get('trustedproxy.headers');
        switch ($headers) {
            case 'HEADER_X_FORWARDED_AWS_ELB':
            case Request::HEADER_X_FORWARDED_AWS_ELB:
                return Request::HEADER_X_FORWARDED_AWS_ELB;
                break;
            case 'HEADER_FORWARDED':
            case Request::HEADER_FORWARDED:
                return Request::HEADER_FORWARDED;
                break;
            default:
                return Request::HEADER_X_FORWARDED_ALL;
        }
        return $headers;
    }
}
