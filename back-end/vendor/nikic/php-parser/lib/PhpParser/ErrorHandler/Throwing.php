<?php declare(strict_types=1);
namespace PhpParser\ErrorHandler;
use PhpParser\Error;
use PhpParser\ErrorHandler;
class Throwing implements ErrorHandler
{
    public function handleError(Error $error) {
        throw $error;
    }
}
