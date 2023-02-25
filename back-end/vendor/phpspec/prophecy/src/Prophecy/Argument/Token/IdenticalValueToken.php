<?php
namespace Prophecy\Argument\Token;
use Prophecy\Util\StringUtil;
class IdenticalValueToken implements TokenInterface
{
    private $value;
    private $string;
    private $util;
    public function __construct($value, StringUtil $util = null)
    {
        $this->value = $value;
        $this->util  = $util ?: new StringUtil();
    }
    public function scoreArgument($argument)
    {
        return $argument === $this->value ? 11 : false;
    }
    public function isLast()
    {
        return false;
    }
    public function __toString()
    {
        if (null === $this->string) {
            $this->string = sprintf('identical(%s)', $this->util->stringify($this->value));
        }
        return $this->string;
    }
}
