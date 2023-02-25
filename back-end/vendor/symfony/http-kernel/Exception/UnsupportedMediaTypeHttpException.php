<?php
namespace Symfony\Component\HttpKernel\Exception;
class UnsupportedMediaTypeHttpException extends HttpException
{
    public function __construct(string $message = null, \Exception $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(415, $message, $previous, $headers, $code);
    }
}
