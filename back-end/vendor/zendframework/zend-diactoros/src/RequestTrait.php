<?php
namespace Zend\Diactoros;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use function array_keys;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function preg_match;
use function sprintf;
use function strtolower;
trait RequestTrait
{
    use MessageTrait;
    private $method = '';
    private $requestTarget;
    private $uri;
    private function initialize($uri = null, $method = null, $body = 'php:
    {
        $this->validateMethod($method);
        $this->method = $method ?: '';
        $this->uri    = $this->createUri($uri);
        $this->stream = $this->getStream($body, 'wb+');
        $this->setHeaders($headers);
        if (! $this->hasHeader('Host') && $this->uri->getHost()) {
            $this->headerNames['host'] = 'Host';
            $this->headers['Host'] = [$this->getHostFromUri()];
        }
    }
    private function createUri($uri)
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        }
        if (is_string($uri)) {
            return new Uri($uri);
        }
        if ($uri === null) {
            return new Uri();
        }
        throw new InvalidArgumentException(
            'Invalid URI provided; must be null, a string, or a Psr\Http\Message\UriInterface instance'
        );
    }
    public function getRequestTarget()
    {
        if (null !== $this->requestTarget) {
            return $this->requestTarget;
        }
        $target = $this->uri->getPath();
        if ($this->uri->getQuery()) {
            $target .= '?' . $this->uri->getQuery();
        }
        if (empty($target)) {
            $target = '/';
        }
        return $target;
    }
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Invalid request target provided; cannot contain whitespace'
            );
        }
        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
    }
    public function getMethod()
    {
        return $this->method;
    }
    public function withMethod($method)
    {
        $this->validateMethod($method);
        $new = clone $this;
        $new->method = $method;
        return $new;
    }
    public function getUri()
    {
        return $this->uri;
    }
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $new = clone $this;
        $new->uri = $uri;
        if ($preserveHost && $this->hasHeader('Host')) {
            return $new;
        }
        if (! $uri->getHost()) {
            return $new;
        }
        $host = $uri->getHost();
        if ($uri->getPort()) {
            $host .= ':' . $uri->getPort();
        }
        $new->headerNames['host'] = 'Host';
        foreach (array_keys($new->headers) as $header) {
            if (strtolower($header) === 'host') {
                unset($new->headers[$header]);
            }
        }
        $new->headers['Host'] = [$host];
        return $new;
    }
    private function validateMethod($method)
    {
        if (null === $method) {
            return;
        }
        if (! is_string($method)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be a string, received %s',
                (is_object($method) ? get_class($method) : gettype($method))
            ));
        }
        if (! preg_match('/^[!#$%&\'*+.^_`\|~0-9a-z-]+$/i', $method)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method "%s" provided',
                $method
            ));
        }
    }
    private function getHostFromUri()
    {
        $host  = $this->uri->getHost();
        $host .= $this->uri->getPort() ? ':' . $this->uri->getPort() : '';
        return $host;
    }
}
