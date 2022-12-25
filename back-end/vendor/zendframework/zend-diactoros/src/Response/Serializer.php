<?php
namespace Zend\Diactoros\Response;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;
use Zend\Diactoros\AbstractSerializer;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;
use function preg_match;
use function sprintf;
final class Serializer extends AbstractSerializer
{
    public static function fromString($message)
    {
        $stream = new Stream('php:
        $stream->write($message);
        return static::fromStream($stream);
    }
    public static function fromStream(StreamInterface $stream)
    {
        if (! $stream->isReadable() || ! $stream->isSeekable()) {
            throw new InvalidArgumentException('Message stream must be both readable and seekable');
        }
        $stream->rewind();
        list($version, $status, $reasonPhrase) = self::getStatusLine($stream);
        list($headers, $body)                  = self::splitStream($stream);
        return (new Response($body, $status, $headers))
            ->withProtocolVersion($version)
            ->withStatus((int) $status, $reasonPhrase);
    }
    public static function toString(ResponseInterface $response)
    {
        $reasonPhrase = $response->getReasonPhrase();
        $headers      = self::serializeHeaders($response->getHeaders());
        $body         = (string) $response->getBody();
        $format       = 'HTTP/%s %d%s%s%s';
        if (! empty($headers)) {
            $headers = "\r\n" . $headers;
        }
        $headers .= "\r\n\r\n";
        return sprintf(
            $format,
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            ($reasonPhrase ? ' ' . $reasonPhrase : ''),
            $headers,
            $body
        );
    }
    private static function getStatusLine(StreamInterface $stream)
    {
        $line = self::getLine($stream);
        if (! preg_match(
            '#^HTTP/(?P<version>[1-9]\d*\.\d) (?P<status>[1-5]\d{2})(\s+(?P<reason>.+))?$#',
            $line,
            $matches
        )) {
            throw new UnexpectedValueException('No status line detected');
        }
        return [$matches['version'], $matches['status'], isset($matches['reason']) ? $matches['reason'] : ''];
    }
}
