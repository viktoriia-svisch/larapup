<?php
namespace Zend\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;
use function sprintf;
final class ArraySerializer
{
    public static function toArray(ResponseInterface $response)
    {
        return [
            'status_code'      => $response->getStatusCode(),
            'reason_phrase'    => $response->getReasonPhrase(),
            'protocol_version' => $response->getProtocolVersion(),
            'headers'          => $response->getHeaders(),
            'body'             => (string) $response->getBody(),
        ];
    }
    public static function fromArray(array $serializedResponse)
    {
        try {
            $body = new Stream('php:
            $body->write(self::getValueFromKey($serializedResponse, 'body'));
            $statusCode      = self::getValueFromKey($serializedResponse, 'status_code');
            $headers         = self::getValueFromKey($serializedResponse, 'headers');
            $protocolVersion = self::getValueFromKey($serializedResponse, 'protocol_version');
            $reasonPhrase    = self::getValueFromKey($serializedResponse, 'reason_phrase');
            return (new Response($body, $statusCode, $headers))
                ->withProtocolVersion($protocolVersion)
                ->withStatus($statusCode, $reasonPhrase);
        } catch (\Exception $exception) {
            throw new UnexpectedValueException('Cannot deserialize response', null, $exception);
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
