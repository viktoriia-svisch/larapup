<?php
namespace Prophecy\Argument\Token;
class ApproximateValueToken implements TokenInterface
{
    private $value;
    private $precision;
    public function __construct($value, $precision = 0)
    {
        $this->value = $value;
        $this->precision = $precision;
    }
    public function scoreArgument($argument)
    {
        return round($argument, $this->precision) === round($this->value, $this->precision) ? 10 : false;
    }
    public function isLast()
    {
        return false;
    }
    public function __toString()
    {
        return sprintf('≅%s', round($this->value, $this->precision));
    }
}
