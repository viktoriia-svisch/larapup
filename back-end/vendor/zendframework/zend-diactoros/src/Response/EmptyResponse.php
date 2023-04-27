<?php
namespace Zend\Diactoros\Response;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;
class EmptyResponse extends Response
{
    public function __construct($status = 204, array $headers = [])
    {
        $body = new Stream('php:
        parent::__construct($body, $status, $headers);
    }
    public static function withHeaders(array $headers)
    {
        return new static(204, $headers);
    }
}
