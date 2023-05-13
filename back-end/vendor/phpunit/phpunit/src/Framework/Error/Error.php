<?php
namespace PHPUnit\Framework\Error;
use PHPUnit\Framework\Exception;
class Error extends Exception
{
    public function __construct(string $message, int $code, string $file, int $line, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->file = $file;
        $this->line = $line;
    }
}
