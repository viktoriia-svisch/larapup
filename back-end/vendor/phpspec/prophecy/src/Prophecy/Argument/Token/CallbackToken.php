<?php
namespace Prophecy\Argument\Token;
use Prophecy\Exception\InvalidArgumentException;
class CallbackToken implements TokenInterface
{
    private $callback;
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException(sprintf(
                'Callable expected as an argument to CallbackToken, but got %s.',
                gettype($callback)
            ));
        }
        $this->callback = $callback;
    }
    public function scoreArgument($argument)
    {
        return call_user_func($this->callback, $argument) ? 7 : false;
    }
    public function isLast()
    {
        return false;
    }
    public function __toString()
    {
        return 'callback()';
    }
}
