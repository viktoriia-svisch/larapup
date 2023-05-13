<?php
namespace Symfony\Component\HttpKernel\Exception;
class HttpException extends \RuntimeException implements HttpExceptionInterface
{
    private $statusCode;
    private $headers;
    public function __construct(int $statusCode, string $message = null, \Exception $previous = null, array $headers = [], ?int $code = 0)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        parent::__construct($message, $code, $previous);
    }
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    public function getHeaders()
    {
        return $this->headers;
    }
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }
}
