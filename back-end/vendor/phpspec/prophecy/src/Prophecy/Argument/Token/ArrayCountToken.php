<?php
namespace Prophecy\Argument\Token;
class ArrayCountToken implements TokenInterface
{
    private $count;
    public function __construct($value)
    {
        $this->count = $value;
    }
    public function scoreArgument($argument)
    {
        return $this->isCountable($argument) && $this->hasProperCount($argument) ? 6 : false;
    }
    public function isLast()
    {
        return false;
    }
    public function __toString()
    {
        return sprintf('count(%s)', $this->count);
    }
    private function isCountable($argument)
    {
        return (is_array($argument) || $argument instanceof \Countable);
    }
    private function hasProperCount($argument)
    {
        return $this->count === count($argument);
    }
}
