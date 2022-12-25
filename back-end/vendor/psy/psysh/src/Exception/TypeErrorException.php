<?php
namespace Psy\Exception;
class TypeErrorException extends \Exception implements Exception
{
    private $rawMessage;
    public function __construct($message = '', $code = 0)
    {
        $this->rawMessage = $message;
        $message = \preg_replace('/, called in .*?: eval\\(\\)\'d code/', '', $message);
        parent::__construct(\sprintf('TypeError: %s', $message), $code);
    }
    public function getRawMessage()
    {
        return $this->rawMessage;
    }
    public static function fromTypeError(\TypeError $e)
    {
        return new self($e->getMessage(), $e->getCode());
    }
}
