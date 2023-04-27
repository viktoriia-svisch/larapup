<?php
namespace Prophecy\Argument\Token;
class AnyValuesToken implements TokenInterface
{
    public function scoreArgument($argument)
    {
        return 2;
    }
    public function isLast()
    {
        return true;
    }
    public function __toString()
    {
        return '* [, ...]';
    }
}
