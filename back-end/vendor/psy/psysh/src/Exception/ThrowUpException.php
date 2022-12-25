<?php
namespace Psy\Exception;
class ThrowUpException extends \Exception implements Exception
{
    public function __construct(\Exception $exception)
    {
        $message = \sprintf("Throwing %s with message '%s'", \get_class($exception), $exception->getMessage());
        parent::__construct($message, $exception->getCode(), $exception);
    }
    public function getRawMessage()
    {
        return $this->getPrevious()->getMessage();
    }
    public static function fromThrowable($throwable)
    {
        if ($throwable instanceof \Error) {
            $throwable = ErrorException::fromError($throwable);
        }
        if (!$throwable instanceof \Exception) {
            throw new \InvalidArgumentException('throw-up can only throw Exceptions and Errors');
        }
        return new self($throwable);
    }
}
