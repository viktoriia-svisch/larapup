<?php
namespace Symfony\Component\HttpKernel\Exception;
class LengthRequiredHttpException extends HttpException
{
    public function __construct(string $message = null, \Exception $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(411, $message, $previous, $headers, $code);
    }
}
