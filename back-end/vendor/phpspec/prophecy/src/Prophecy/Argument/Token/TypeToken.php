<?php
namespace Prophecy\Argument\Token;
use Prophecy\Exception\InvalidArgumentException;
class TypeToken implements TokenInterface
{
    private $type;
    public function __construct($type)
    {
        $checker = "is_{$type}";
        if (!function_exists($checker) && !interface_exists($type) && !class_exists($type)) {
            throw new InvalidArgumentException(sprintf(
                'Type or class name expected as an argument to TypeToken, but got %s.', $type
            ));
        }
        $this->type = $type;
    }
    public function scoreArgument($argument)
    {
        $checker = "is_{$this->type}";
        if (function_exists($checker)) {
            return call_user_func($checker, $argument) ? 5 : false;
        }
        return $argument instanceof $this->type ? 5 : false;
    }
    public function isLast()
    {
        return false;
    }
    public function __toString()
    {
        return sprintf('type(%s)', $this->type);
    }
}
