<?php
namespace Zend\Diactoros\Request;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;
use Zend\Diactoros\AbstractSerializer;
use Zend\Diactoros\Request;
use Zend\Diactoros\Stream;
use Zend\Diactoros\Uri;
use function preg_match;
use function sprintf;
final class Serializer extends AbstractSerializer
{
    public static function fromString($message)
    {
        $stream = new Stream('php:
        $stream->write($message);
        return self::fromStream($stream);
    }
    public static function fromStream(StreamInterface $stream)
    {
        if (! $stream->isReadable() || ! $stream->isSeekable()) {
            throw new InvalidArgumentException('Message stream must be both readable and seekable');
        }
        $stream->rewind();
        list($method, $requestTarget, $version) = self::getRequestLine($stream);
        $uri = self::createUriFromRequestTarget($requestTarget);
        list($headers, $body) = self::splitStream($stream);
        return (new Request($uri, $method, $body, $headers))
            ->withProtocolVersion($version)
            ->withRequestTarget($requestTarget);
    }
    public static function toString(RequestInterface $request)
    {
        $httpMethod = $request->getMethod();
        if (empty($httpMethod)) {
            throw new UnexpectedValueException('Object can not be serialized because HTTP method is empty');
        }
        $headers = self::serializeHeaders($request->getHeaders());
        $body    = (string) $request->getBody();
        $format  = '%s %s HTTP/%s%s%s';
        if (! empty($headers)) {
            $headers = "\r\n" . $headers;
        }
        if (! empty($body)) {
            $headers .= "\r\n\r\n";
        }
        return sprintf(
            $format,
            $httpMethod,
            $request->getRequestTarget(),
            $request->getProtocolVersion(),
            $headers,
            $body
        );
    }
    private static function getRequestLine(StreamInterface $stream)
    {
        $requestLine = self::getLine($stream);
        if (! preg_match(
            '#^(?P<method>[!\#$%&\'*+.^_`|~a-zA-Z0-9-]+) (?P<target>[^\s]+) HTTP/(?P<version>[1-9]\d*\.\d+)$#',
            $requestLine,
            $matches
        )) {
            throw new UnexpectedValueException('Invalid request line detected');
        }
        return [$matches['method'], $matches['target'], $matches['version']];
    }
    private static function createUriFromRequestTarget($requestTarget)
    {
        if (preg_match('#^https?:
            return new Uri($requestTarget);
        }
        if (preg_match('#^(\*|[^/])#', $requestTarget)) {
            return new Uri();
        }
        return new Uri($requestTarget);
    }
}
