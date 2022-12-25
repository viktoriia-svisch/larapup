<?php
namespace Zend\Diactoros;
use function array_change_key_case;
use function array_key_exists;
use function explode;
use function implode;
use function is_array;
use function ltrim;
use function preg_match;
use function preg_replace;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
function marshalUriFromSapi(array $server, array $headers)
{
    $getHeaderFromArray = function ($name, array $headers, $default = null) {
        $header  = strtolower($name);
        $headers = array_change_key_case($headers, CASE_LOWER);
        if (array_key_exists($header, $headers)) {
            $value = is_array($headers[$header]) ? implode(', ', $headers[$header]) : $headers[$header];
            return $value;
        }
        return $default;
    };
    $marshalHostAndPort = function (array $headers, array $server) use ($getHeaderFromArray) {
        $marshalHostAndPortFromHeader = function ($host) {
            if (is_array($host)) {
                $host = implode(', ', $host);
            }
            $port = null;
            if (preg_match('|\:(\d+)$|', $host, $matches)) {
                $host = substr($host, 0, -1 * (strlen($matches[1]) + 1));
                $port = (int) $matches[1];
            }
            return [$host, $port];
        };
        $marshalIpv6HostAndPort = function (array $server, $host, $port) {
            $host = '[' . $server['SERVER_ADDR'] . ']';
            $port = $port ?: 80;
            if ($port . ']' === substr($host, strrpos($host, ':') + 1)) {
                $port = null;
            }
            return [$host, $port];
        };
        static $defaults = ['', null];
        if ($getHeaderFromArray('host', $headers, false)) {
            return $marshalHostAndPortFromHeader($getHeaderFromArray('host', $headers));
        }
        if (! isset($server['SERVER_NAME'])) {
            return $defaults;
        }
        $host = $server['SERVER_NAME'];
        $port = isset($server['SERVER_PORT']) ? (int) $server['SERVER_PORT'] : null;
        if (! isset($server['SERVER_ADDR'])
            || ! preg_match('/^\[[0-9a-fA-F\:]+\]$/', $host)
        ) {
            return [$host, $port];
        }
        return $marshalIpv6HostAndPort($server, $host, $port);
    };
    $marshalRequestPath = function (array $server) {
        $iisUrlRewritten = array_key_exists('IIS_WasUrlRewritten', $server) ? $server['IIS_WasUrlRewritten'] : null;
        $unencodedUrl    = array_key_exists('UNENCODED_URL', $server) ? $server['UNENCODED_URL'] : '';
        if ('1' === $iisUrlRewritten && ! empty($unencodedUrl)) {
            return $unencodedUrl;
        }
        $requestUri = array_key_exists('REQUEST_URI', $server) ? $server['REQUEST_URI'] : null;
        if ($requestUri !== null) {
            return preg_replace('#^[^/:]+:
        }
        $origPathInfo = array_key_exists('ORIG_PATH_INFO', $server) ? $server['ORIG_PATH_INFO'] : null;
        if (empty($origPathInfo)) {
            return '/';
        }
        return $origPathInfo;
    };
    $uri = new Uri('');
    $scheme = 'http';
    if (array_key_exists('HTTPS', $server)) {
        $https = $server['HTTPS'];
    } elseif (array_key_exists('https', $server)) {
        $https = $server['https'];
    } else {
        $https = false;
    }
    if (($https && 'off' !== strtolower($https))
        || strtolower($getHeaderFromArray('x-forwarded-proto', $headers, false)) === 'https'
    ) {
        $scheme = 'https';
    }
    $uri = $uri->withScheme($scheme);
    list($host, $port) = $marshalHostAndPort($headers, $server);
    if (! empty($host)) {
        $uri = $uri->withHost($host);
        if (! empty($port)) {
            $uri = $uri->withPort($port);
        }
    }
    $path = $marshalRequestPath($server);
    $path = explode('?', $path, 2)[0];
    $query = '';
    if (isset($server['QUERY_STRING'])) {
        $query = ltrim($server['QUERY_STRING'], '?');
    }
    $fragment = '';
    if (strpos($path, '#') !== false) {
        list($path, $fragment) = explode('#', $path, 2);
    }
    return $uri
        ->withPath($path)
        ->withFragment($fragment)
        ->withQuery($query);
}
