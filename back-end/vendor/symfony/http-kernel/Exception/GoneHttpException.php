<?php
namespace Symfony\Component\HttpKernel\Exception;
class GoneHttpException extends HttpException
{
    public function __construct(string $message = null, \Exception $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(410, $message, $previous, $headers, $code);
    }
}
