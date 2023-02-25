<?php
namespace Prophecy\Argument\Token;
interface TokenInterface
{
    public function scoreArgument($argument);
    public function isLast();
    public function __toString();
}
