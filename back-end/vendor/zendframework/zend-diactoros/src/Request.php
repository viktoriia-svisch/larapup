<?php
namespace Zend\Diactoros;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use function strtolower;
class Request implements RequestInterface
{
    use RequestTrait;
    public function __construct($uri = null, $method = null, $body = 'php:
    {
        $this->initialize($uri, $method, $body, $headers);
    }
    public function getHeaders()
    {
        $headers = $this->headers;
        if (! $this->hasHeader('host')
            && $this->uri->getHost()
        ) {
            $headers['Host'] = [$this->getHostFromUri()];
        }
        return $headers;
    }
    public function getHeader($header)
    {
        if (! $this->hasHeader($header)) {
            if (strtolower($header) === 'host'
                && $this->uri->getHost()
            ) {
                return [$this->getHostFromUri()];
            }
            return [];
        }
        $header = $this->headerNames[strtolower($header)];
        return $this->headers[$header];
    }
}
