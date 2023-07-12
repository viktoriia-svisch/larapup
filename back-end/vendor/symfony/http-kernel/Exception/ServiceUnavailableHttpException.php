<?php
namespace Symfony\Component\HttpKernel\Exception;
class ServiceUnavailableHttpException extends HttpException
{
    public function __construct($retryAfter = null, string $message = null, \Exception $previous = null, ?int $code = 0, array $headers = [])
    {
        if ($retryAfter) {
            $headers['Retry-After'] = $retryAfter;
        }
        parent::__construct(503, $message, $previous, $headers, $code);
    }
}
