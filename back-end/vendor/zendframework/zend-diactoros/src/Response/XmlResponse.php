<?php
namespace Zend\Diactoros\Response;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;
class XmlResponse extends Response
{
    use InjectContentTypeTrait;
    public function __construct(
        $xml,
        $status = 200,
        array $headers = []
    ) {
        parent::__construct(
            $this->createBody($xml),
            $status,
            $this->injectContentType('application/xml; charset=utf-8', $headers)
        );
    }
    private function createBody($xml)
    {
        if ($xml instanceof StreamInterface) {
            return $xml;
        }
        if (! is_string($xml)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid content (%s) provided to %s',
                (is_object($xml) ? get_class($xml) : gettype($xml)),
                __CLASS__
            ));
        }
        $body = new Stream('php:
        $body->write($xml);
        $body->rewind();
        return $body;
    }
}
