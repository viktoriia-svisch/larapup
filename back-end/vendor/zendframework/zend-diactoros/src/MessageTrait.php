<?php
namespace Zend\Diactoros;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use function array_map;
use function array_merge;
use function get_class;
use function gettype;
use function implode;
use function is_array;
use function is_object;
use function is_resource;
use function is_string;
use function preg_match;
use function sprintf;
use function strtolower;
trait MessageTrait
{
    protected $headers = [];
    protected $headerNames = [];
    private $protocol = '1.1';
    private $stream;
    public function getProtocolVersion()
    {
        return $this->protocol;
    }
    public function withProtocolVersion($version)
    {
        $this->validateProtocolVersion($version);
        $new = clone $this;
        $new->protocol = $version;
        return $new;
    }
    public function getHeaders()
    {
        return $this->headers;
    }
    public function hasHeader($header)
    {
        return isset($this->headerNames[strtolower($header)]);
    }
    public function getHeader($header)
    {
        if (! $this->hasHeader($header)) {
            return [];
        }
        $header = $this->headerNames[strtolower($header)];
        return $this->headers[$header];
    }
    public function getHeaderLine($name)
    {
        $value = $this->getHeader($name);
        if (empty($value)) {
            return '';
        }
        return implode(',', $value);
    }
    public function withHeader($header, $value)
    {
        $this->assertHeader($header);
        $normalized = strtolower($header);
        $new = clone $this;
        if ($new->hasHeader($header)) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }
        $value = $this->filterHeaderValue($value);
        $new->headerNames[$normalized] = $header;
        $new->headers[$header]         = $value;
        return $new;
    }
    public function withAddedHeader($header, $value)
    {
        $this->assertHeader($header);
        if (! $this->hasHeader($header)) {
            return $this->withHeader($header, $value);
        }
        $header = $this->headerNames[strtolower($header)];
        $new = clone $this;
        $value = $this->filterHeaderValue($value);
        $new->headers[$header] = array_merge($this->headers[$header], $value);
        return $new;
    }
    public function withoutHeader($header)
    {
        if (! $this->hasHeader($header)) {
            return clone $this;
        }
        $normalized = strtolower($header);
        $original   = $this->headerNames[$normalized];
        $new = clone $this;
        unset($new->headers[$original], $new->headerNames[$normalized]);
        return $new;
    }
    public function getBody()
    {
        return $this->stream;
    }
    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        $new->stream = $body;
        return $new;
    }
    private function getStream($stream, $modeIfNotInstance)
    {
        if ($stream instanceof StreamInterface) {
            return $stream;
        }
        if (! is_string($stream) && ! is_resource($stream)) {
            throw new InvalidArgumentException(
                'Stream must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Psr\Http\Message\StreamInterface implementation'
            );
        }
        return new Stream($stream, $modeIfNotInstance);
    }
    private function setHeaders(array $originalHeaders)
    {
        $headerNames = $headers = [];
        foreach ($originalHeaders as $header => $value) {
            $value = $this->filterHeaderValue($value);
            $this->assertHeader($header);
            $headerNames[strtolower($header)] = $header;
            $headers[$header] = $value;
        }
        $this->headerNames = $headerNames;
        $this->headers = $headers;
    }
    private function validateProtocolVersion($version)
    {
        if (empty($version)) {
            throw new InvalidArgumentException(
                'HTTP protocol version can not be empty'
            );
        }
        if (! is_string($version)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP protocol version; must be a string, received %s',
                (is_object($version) ? get_class($version) : gettype($version))
            ));
        }
        if (! preg_match('#^(1\.[01]|2)$#', $version)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP protocol version "%s" provided',
                $version
            ));
        }
    }
    private function filterHeaderValue($values)
    {
        if (! is_array($values)) {
            $values = [$values];
        }
        if ([] === $values) {
            throw new InvalidArgumentException(
                'Invalid header value: must be a string or array of strings; '
                . 'cannot be an empty array'
            );
        }
        return array_map(function ($value) {
            HeaderSecurity::assertValid($value);
            return (string) $value;
        }, array_values($values));
    }
    private function assertHeader($name)
    {
        HeaderSecurity::assertValidName($name);
    }
}
