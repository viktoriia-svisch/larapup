<?php
namespace Illuminate\Http\Exceptions;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
class ThrottleRequestsException extends HttpException
{
    public function __construct($message = null, Exception $previous = null, array $headers = [], $code = 0)
    {
        parent::__construct(429, $message, $previous, $headers, $code);
    }
}
