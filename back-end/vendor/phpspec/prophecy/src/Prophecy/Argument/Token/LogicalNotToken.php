<?php
namespace Prophecy\Argument\Token;
class LogicalNotToken implements TokenInterface
{
    private $token;
    public function __construct($value)
    {
        $this->token = $value instanceof TokenInterface? $value : new ExactValueToken($value);
    }
    public function scoreArgument($argument)
    {
        return false === $this->token->scoreArgument($argument) ? 4 : false;
    }
    public function isLast()
    {
        return $this->token->isLast();
    }
    public function getOriginatingToken()
    {
        return $this->token;
    }
    public function __toString()
    {
        return sprintf('not(%s)', $this->token);
    }
}
