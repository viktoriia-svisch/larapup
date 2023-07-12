<?php
namespace Illuminate\Routing;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Routing\Exceptions\UrlGenerationException;
class RouteUrlGenerator
{
    protected $url;
    protected $request;
    public $defaultParameters = [];
    public $dontEncode = [
        '%2F' => '/',
        '%40' => '@',
        '%3A' => ':',
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
        '%3F' => '?',
        '%26' => '&',
        '%23' => '#',
        '%25' => '%',
    ];
    public function __construct($url, $request)
    {
        $this->url = $url;
        $this->request = $request;
    }
    public function to($route, $parameters = [], $absolute = false)
    {
        $domain = $this->getRouteDomain($route, $parameters);
        $uri = $this->addQueryString($this->url->format(
            $root = $this->replaceRootParameters($route, $domain, $parameters),
            $this->replaceRouteParameters($route->uri(), $parameters),
            $route
        ), $parameters);
        if (preg_match('/\{.*?\}/', $uri)) {
            throw UrlGenerationException::forMissingParameters($route);
        }
        $uri = strtr(rawurlencode($uri), $this->dontEncode);
        if (! $absolute) {
            $uri = preg_replace('#^(
            if ($base = $this->request->getBaseUrl()) {
                $uri = preg_replace('#^'.$base.'#i', '', $uri);
            }
            return '/'.ltrim($uri, '/');
        }
        return $uri;
    }
    protected function getRouteDomain($route, &$parameters)
    {
        return $route->getDomain() ? $this->formatDomain($route, $parameters) : null;
    }
    protected function formatDomain($route, &$parameters)
    {
        return $this->addPortToDomain(
            $this->getRouteScheme($route).$route->getDomain()
        );
    }
    protected function getRouteScheme($route)
    {
        if ($route->httpOnly()) {
            return 'http:
        } elseif ($route->httpsOnly()) {
            return 'https:
        }
        return $this->url->formatScheme();
    }
    protected function addPortToDomain($domain)
    {
        $secure = $this->request->isSecure();
        $port = (int) $this->request->getPort();
        return ($secure && $port === 443) || (! $secure && $port === 80)
                    ? $domain : $domain.':'.$port;
    }
    protected function replaceRootParameters($route, $domain, &$parameters)
    {
        $scheme = $this->getRouteScheme($route);
        return $this->replaceRouteParameters(
            $this->url->formatRoot($scheme, $domain), $parameters
        );
    }
    protected function replaceRouteParameters($path, array &$parameters)
    {
        $path = $this->replaceNamedParameters($path, $parameters);
        $path = preg_replace_callback('/\{.*?\}/', function ($match) use (&$parameters) {
            return (empty($parameters) && ! Str::endsWith($match[0], '?}'))
                        ? $match[0]
                        : array_shift($parameters);
        }, $path);
        return trim(preg_replace('/\{.*?\?\}/', '', $path), '/');
    }
    protected function replaceNamedParameters($path, &$parameters)
    {
        return preg_replace_callback('/\{(.*?)\??\}/', function ($m) use (&$parameters) {
            if (isset($parameters[$m[1]])) {
                return Arr::pull($parameters, $m[1]);
            } elseif (isset($this->defaultParameters[$m[1]])) {
                return $this->defaultParameters[$m[1]];
            }
            return $m[0];
        }, $path);
    }
    protected function addQueryString($uri, array $parameters)
    {
        if (! is_null($fragment = parse_url($uri, PHP_URL_FRAGMENT))) {
            $uri = preg_replace('/#.*/', '', $uri);
        }
        $uri .= $this->getRouteQueryString($parameters);
        return is_null($fragment) ? $uri : $uri."#{$fragment}";
    }
    protected function getRouteQueryString(array $parameters)
    {
        if (count($parameters) === 0) {
            return '';
        }
        $query = Arr::query(
            $keyed = $this->getStringParameters($parameters)
        );
        if (count($keyed) < count($parameters)) {
            $query .= '&'.implode(
                '&', $this->getNumericParameters($parameters)
            );
        }
        return '?'.trim($query, '&');
    }
    protected function getStringParameters(array $parameters)
    {
        return array_filter($parameters, 'is_string', ARRAY_FILTER_USE_KEY);
    }
    protected function getNumericParameters(array $parameters)
    {
        return array_filter($parameters, 'is_numeric', ARRAY_FILTER_USE_KEY);
    }
    public function defaults(array $defaults)
    {
        $this->defaultParameters = array_merge(
            $this->defaultParameters, $defaults
        );
    }
}
