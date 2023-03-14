<?php
namespace Tymon\JWTAuth\Exceptions;
use Exception;
use Tymon\JWTAuth\Claims\Claim;
class InvalidClaimException extends JWTException
{
    public function __construct(Claim $claim, $code = 0, Exception $previous = null)
    {
        parent::__construct('Invalid value provided for claim ['.$claim->getName().']', $code, $previous);
    }
}
