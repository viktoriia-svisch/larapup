<?php
namespace Zend\Diactoros\Request;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;
use Zend\Diactoros\Request;
use Zend\Diactoros\Stream;
use function sprintf;
final class ArraySerializer
{
    public static function toArray(RequestInterface $request)
    {
        return [
            'method'           => $request->getMethod(),
            'request_target'   => $request->getRequestTarget(),
            'uri'              => (string) $request->getUri(),
            'protocol_version' => $request->getProtocolVersion(),
            'headers'          => $request->getHeaders(),
            'body'             => (string) $request->getBody(),
        ];
    }
    public static function fromArray(array $serializedRequest)
    {
        try {
            $uri             = self::getValueFromKey($serializedRequest, 'uri');
            $method          = self::getValueFromKey($serializedRequest, 'method');
            $body            = new Stream('php:
            $body->write(self::getValueFromKey($serializedRequest, 'body'));
            $headers         = self::getValueFromKey($serializedRequest, 'headers');
            $requestTarget   = self::getValueFromKey($serializedRequest, 'request_target');
            $protocolVersion = self::getValueFromKey($serializedRequest, 'protocol_version');
            return (new Request($uri, $method, $body, $headers))
                ->withRequestTarget($requestTarget)
                ->withProtocolVersion($protocolVersion);
        } catch (\Exception $exception) {
            throw new UnexpectedValueException('Cannot deserialize request', null, $exception);
        }
    }
    private static function getValueFromKey(array $data, $key, $message = null)
    {
        if (isset($data[$key])) {
            return $data[$key];
        }
        if ($message === null) {
            $message = sprintf('Missing "%s" key in serialized request', $key);
        }
        throw new UnexpectedValueException($message);
    }
}
