<?php
namespace Prophecy\Argument\Token;
class AnyValueToken implements TokenInterface
{
    public function scoreArgument($argument)
    {
        return 3;
    }
    public function isLast()
    {
        return false;
    }
    public function __toString()
    {
        return '*';
    }
}
