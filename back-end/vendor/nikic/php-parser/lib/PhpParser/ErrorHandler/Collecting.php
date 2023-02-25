<?php declare(strict_types=1);
namespace PhpParser\ErrorHandler;
use PhpParser\Error;
use PhpParser\ErrorHandler;
class Collecting implements ErrorHandler
{
    private $errors = [];
    public function handleError(Error $error) {
        $this->errors[] = $error;
    }
    public function getErrors() : array {
        return $this->errors;
    }
    public function hasErrors() : bool {
        return !empty($this->errors);
    }
    public function clearErrors() {
        $this->errors = [];
    }
}
