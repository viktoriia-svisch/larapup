<?php
namespace Zend\Diactoros\Response;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;
class RedirectResponse extends Response
{
    public function __construct($uri, $status = 302, array $headers = [])
    {
        if (! is_string($uri) && ! $uri instanceof UriInterface) {
            throw new InvalidArgumentException(sprintf(
                'Uri provided to %s MUST be a string or Psr\Http\Message\UriInterface instance; received "%s"',
                __CLASS__,
                (is_object($uri) ? get_class($uri) : gettype($uri))
            ));
        }
        $headers['location'] = [(string) $uri];
        parent::__construct('php:
    }
}
