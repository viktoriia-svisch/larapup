<?php
namespace Symfony\Component\HttpKernel\Exception;
class UnprocessableEntityHttpException extends HttpException
{
    public function __construct(string $message = null, \Exception $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(422, $message, $previous, $headers, $code);
    }
}
