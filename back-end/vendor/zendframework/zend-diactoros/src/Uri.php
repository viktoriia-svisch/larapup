<?php
namespace Zend\Diactoros;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use function array_key_exists;
use function array_keys;
use function count;
use function explode;
use function get_class;
use function gettype;
use function implode;
use function is_numeric;
use function is_object;
use function is_string;
use function ltrim;
use function parse_url;
use function preg_replace;
use function preg_replace_callback;
use function rawurlencode;
use function sprintf;
use function strpos;
use function strtolower;
use function substr;
class Uri implements UriInterface
{
    const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';
    const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~\pL';
    protected $allowedSchemes = [
        'http'  => 80,
        'https' => 443,
    ];
    private $scheme = '';
    private $userInfo = '';
    private $host = '';
    private $port;
    private $path = '';
    private $query = '';
    private $fragment = '';
    private $uriString;
    public function __construct($uri = '')
    {
        if ('' === $uri) {
            return;
        }
        if (! is_string($uri)) {
            throw new InvalidArgumentException(sprintf(
                'URI passed to constructor must be a string; received "%s"',
                is_object($uri) ? get_class($uri) : gettype($uri)
            ));
        }
        $this->parseUri($uri);
    }
    public function __clone()
    {
        $this->uriString = null;
    }
    public function __toString()
    {
        if (null !== $this->uriString) {
            return $this->uriString;
        }
        $this->uriString = static::createUriString(
            $this->scheme,
            $this->getAuthority(),
            $this->getPath(), 
            $this->query,
            $this->fragment
        );
        return $this->uriString;
    }
    public function getScheme()
    {
        return $this->scheme;
    }
    public function getAuthority()
    {
        if ('' === $this->host) {
            return '';
        }
        $authority = $this->host;
        if ('' !== $this->userInfo) {
            $authority = $this->userInfo . '@' . $authority;
        }
        if ($this->isNonStandardPort($this->scheme, $this->host, $this->port)) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }
    public function getUserInfo()
    {
        return $this->userInfo;
    }
    public function getHost()
    {
        return $this->host;
    }
    public function getPort()
    {
        return $this->isNonStandardPort($this->scheme, $this->host, $this->port)
            ? $this->port
            : null;
    }
    public function getPath()
    {
        return $this->path;
    }
    public function getQuery()
    {
        return $this->query;
    }
    public function getFragment()
    {
        return $this->fragment;
    }
    public function withScheme($scheme)
    {
        if (! is_string($scheme)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a string argument; received %s',
                __METHOD__,
                is_object($scheme) ? get_class($scheme) : gettype($scheme)
            ));
        }
        $scheme = $this->filterScheme($scheme);
        if ($scheme === $this->scheme) {
            return $this;
        }
        $new = clone $this;
        $new->scheme = $scheme;
        return $new;
    }
    public function withUserInfo($user, $password = null)
    {
        if (! is_string($user)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a string user argument; received %s',
                __METHOD__,
                is_object($user) ? get_class($user) : gettype($user)
            ));
        }
        if (null !== $password && ! is_string($password)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a string or null password argument; received %s',
                __METHOD__,
                is_object($password) ? get_class($password) : gettype($password)
            ));
        }
        $info = $this->filterUserInfoPart($user);
        if (null !== $password) {
            $info .= ':' . $this->filterUserInfoPart($password);
        }
        if ($info === $this->userInfo) {
            return $this;
        }
        $new = clone $this;
        $new->userInfo = $info;
        return $new;
    }
    public function withHost($host)
    {
        if (! is_string($host)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a string argument; received %s',
                __METHOD__,
                is_object($host) ? get_class($host) : gettype($host)
            ));
        }
        if ($host === $this->host) {
            return $this;
        }
        $new = clone $this;
        $new->host = strtolower($host);
        return $new;
    }
    public function withPort($port)
    {
        if ($port !== null) {
            if (! is_numeric($port) || is_float($port)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid port "%s" specified; must be an integer, an integer string, or null',
                    is_object($port) ? get_class($port) : gettype($port)
                ));
            }
            $port = (int) $port;
        }
        if ($port === $this->port) {
            return $this;
        }
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid port "%d" specified; must be a valid TCP/UDP port',
                $port
            ));
        }
        $new = clone $this;
        $new->port = $port;
        return $new;
    }
    public function withPath($path)
    {
        if (! is_string($path)) {
            throw new InvalidArgumentException(
                'Invalid path provided; must be a string'
            );
        }
        if (strpos($path, '?') !== false) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a query string'
            );
        }
        if (strpos($path, '#') !== false) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a URI fragment'
            );
        }
        $path = $this->filterPath($path);
        if ($path === $this->path) {
            return $this;
        }
        $new = clone $this;
        $new->path = $path;
        return $new;
    }
    public function withQuery($query)
    {
        if (! is_string($query)) {
            throw new InvalidArgumentException(
                'Query string must be a string'
            );
        }
        if (strpos($query, '#') !== false) {
            throw new InvalidArgumentException(
                'Query string must not include a URI fragment'
            );
        }
        $query = $this->filterQuery($query);
        if ($query === $this->query) {
            return $this;
        }
        $new = clone $this;
        $new->query = $query;
        return $new;
    }
    public function withFragment($fragment)
    {
        if (! is_string($fragment)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a string argument; received %s',
                __METHOD__,
                is_object($fragment) ? get_class($fragment) : gettype($fragment)
            ));
        }
        $fragment = $this->filterFragment($fragment);
        if ($fragment === $this->fragment) {
            return $this;
        }
        $new = clone $this;
        $new->fragment = $fragment;
        return $new;
    }
    private function parseUri($uri)
    {
        $parts = parse_url($uri);
        if (false === $parts) {
            throw new \InvalidArgumentException(
                'The source URI string appears to be malformed'
            );
        }
        $this->scheme    = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
        $this->userInfo  = isset($parts['user']) ? $this->filterUserInfoPart($parts['user']) : '';
        $this->host      = isset($parts['host']) ? strtolower($parts['host']) : '';
        $this->port      = isset($parts['port']) ? $parts['port'] : null;
        $this->path      = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
        $this->query     = isset($parts['query']) ? $this->filterQuery($parts['query']) : '';
        $this->fragment  = isset($parts['fragment']) ? $this->filterFragment($parts['fragment']) : '';
        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }
    }
    private static function createUriString($scheme, $authority, $path, $query, $fragment)
    {
        $uri = '';
        if ('' !== $scheme) {
            $uri .= sprintf('%s:', $scheme);
        }
        if ('' !== $authority) {
            $uri .= '
        }
        if ('' !== $path && '/' !== substr($path, 0, 1)) {
            $path = '/' . $path;
        }
        $uri .= $path;
        if ('' !== $query) {
            $uri .= sprintf('?%s', $query);
        }
        if ('' !== $fragment) {
            $uri .= sprintf('#%s', $fragment);
        }
        return $uri;
    }
    private function isNonStandardPort($scheme, $host, $port)
    {
        if ('' === $scheme) {
            return '' === $host || null !== $port;
        }
        if ('' === $host || null === $port) {
            return false;
        }
        return ! isset($this->allowedSchemes[$scheme]) || $port !== $this->allowedSchemes[$scheme];
    }
    private function filterScheme($scheme)
    {
        $scheme = strtolower($scheme);
        $scheme = preg_replace('#:(
        if ('' === $scheme) {
            return '';
        }
        if (! isset($this->allowedSchemes[$scheme])) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported scheme "%s"; must be any empty string or in the set (%s)',
                $scheme,
                implode(', ', array_keys($this->allowedSchemes))
            ));
        }
        return $scheme;
    }
    private function filterUserInfoPart($part)
    {
        return preg_replace_callback(
            '/(?:[^%' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . ']+|%(?![A-Fa-f0-9]{2}))/u',
            [$this, 'urlEncodeChar'],
            $part
        );
    }
    private function filterPath($path)
    {
        $path = preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . ')(:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/u',
            [$this, 'urlEncodeChar'],
            $path
        );
        if ('' === $path) {
            return $path;
        }
        if ($path[0] !== '/') {
            return $path;
        }
        return '/' . ltrim($path, '/');
    }
    private function filterQuery($query)
    {
        if ('' !== $query && strpos($query, '?') === 0) {
            $query = substr($query, 1);
        }
        $parts = explode('&', $query);
        foreach ($parts as $index => $part) {
            list($key, $value) = $this->splitQueryValue($part);
            if ($value === null) {
                $parts[$index] = $this->filterQueryOrFragment($key);
                continue;
            }
            $parts[$index] = sprintf(
                '%s=%s',
                $this->filterQueryOrFragment($key),
                $this->filterQueryOrFragment($value)
            );
        }
        return implode('&', $parts);
    }
    private function splitQueryValue($value)
    {
        $data = explode('=', $value, 2);
        if (! isset($data[1])) {
            $data[] = null;
        }
        return $data;
    }
    private function filterFragment($fragment)
    {
        if ('' !== $fragment && strpos($fragment, '#') === 0) {
            $fragment = '%23' . substr($fragment, 1);
        }
        return $this->filterQueryOrFragment($fragment);
    }
    private function filterQueryOrFragment($value)
    {
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/u',
            [$this, 'urlEncodeChar'],
            $value
        );
    }
    private function urlEncodeChar(array $matches)
    {
        return rawurlencode($matches[0]);
    }
}
