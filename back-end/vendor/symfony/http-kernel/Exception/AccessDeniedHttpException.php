<?php
namespace Symfony\Component\HttpKernel\Exception;
class AccessDeniedHttpException extends HttpException
{
    public function __construct(string $message = null, \Exception $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(403, $message, $previous, $headers, $code);
    }
}
