<?php
namespace Illuminate\Routing\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;
class InvalidSignatureException extends HttpException
{
    public function __construct()
    {
        parent::__construct(403, 'Invalid signature.');
    }
}
