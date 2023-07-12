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
class TextResponse extends Response
{
    use InjectContentTypeTrait;
    public function __construct($text, $status = 200, array $headers = [])
    {
        parent::__construct(
            $this->createBody($text),
            $status,
            $this->injectContentType('text/plain; charset=utf-8', $headers)
        );
    }
    private function createBody($text)
    {
        if ($text instanceof StreamInterface) {
            return $text;
        }
        if (! is_string($text)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid content (%s) provided to %s',
                (is_object($text) ? get_class($text) : gettype($text)),
                __CLASS__
            ));
        }
        $body = new Stream('php:
        $body->write($text);
        $body->rewind();
        return $body;
    }
}
