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
class HtmlResponse extends Response
{
    use InjectContentTypeTrait;
    public function __construct($html, $status = 200, array $headers = [])
    {
        parent::__construct(
            $this->createBody($html),
            $status,
            $this->injectContentType('text/html; charset=utf-8', $headers)
        );
    }
    private function createBody($html)
    {
        if ($html instanceof StreamInterface) {
            return $html;
        }
        if (! is_string($html)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid content (%s) provided to %s',
                (is_object($html) ? get_class($html) : gettype($html)),
                __CLASS__
            ));
        }
        $body = new Stream('php:
        $body->write($html);
        $body->rewind();
        return $body;
    }
}
