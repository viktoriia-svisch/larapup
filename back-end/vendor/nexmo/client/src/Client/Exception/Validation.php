<?php
namespace Nexmo\Client\Exception;
use Throwable;
class Validation extends Request
{
    public function __construct($message = "", $code = 0, Throwable $previous = null, $errors)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }
    public function getValidationErrors() {
        return $this->errors;
    }
}
