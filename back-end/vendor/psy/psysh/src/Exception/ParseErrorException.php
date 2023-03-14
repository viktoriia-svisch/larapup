<?php
namespace Psy\Exception;
class ParseErrorException extends \PhpParser\Error implements Exception
{
    public function __construct($message = '', $line = -1)
    {
        $message = \sprintf('PHP Parse error: %s', $message);
        parent::__construct($message, $line);
    }
    public static function fromParseError(\PhpParser\Error $e)
    {
        return new self($e->getRawMessage(), $e->getStartLine());
    }
}
