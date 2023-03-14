<?php
namespace Symfony\Component\HttpKernel;
class UriSigner
{
    private $secret;
    private $parameter;
    public function __construct(string $secret, string $parameter = '_hash')
    {
        $this->secret = $secret;
        $this->parameter = $parameter;
    }
    public function sign($uri)
    {
        $url = parse_url($uri);
        if (isset($url['query'])) {
            parse_str($url['query'], $params);
        } else {
            $params = [];
        }
        $uri = $this->buildUrl($url, $params);
        $params[$this->parameter] = $this->computeHash($uri);
        return $this->buildUrl($url, $params);
    }
    public function check($uri)
    {
        $url = parse_url($uri);
        if (isset($url['query'])) {
            parse_str($url['query'], $params);
        } else {
            $params = [];
        }
        if (empty($params[$this->parameter])) {
            return false;
        }
        $hash = $params[$this->parameter];
        unset($params[$this->parameter]);
        return $this->computeHash($this->buildUrl($url, $params)) === $hash;
    }
    private function computeHash($uri)
    {
        return base64_encode(hash_hmac('sha256', $uri, $this->secret, true));
    }
    private function buildUrl(array $url, array $params = [])
    {
        ksort($params, SORT_STRING);
        $url['query'] = http_build_query($params, '', '&');
        $scheme = isset($url['scheme']) ? $url['scheme'].':
        $host = isset($url['host']) ? $url['host'] : '';
        $port = isset($url['port']) ? ':'.$url['port'] : '';
        $user = isset($url['user']) ? $url['user'] : '';
        $pass = isset($url['pass']) ? ':'.$url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($url['path']) ? $url['path'] : '';
        $query = isset($url['query']) && $url['query'] ? '?'.$url['query'] : '';
        $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';
        return $scheme.$user.$pass.$host.$port.$path.$query.$fragment;
    }
}
