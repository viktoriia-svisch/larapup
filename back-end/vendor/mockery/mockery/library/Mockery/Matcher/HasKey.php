<?php
namespace Mockery\Matcher;
class HasKey extends MatcherAbstract
{
    public function match(&$actual)
    {
        return array_key_exists($this->_expected, $actual);
    }
    public function __toString()
    {
        return "<HasKey[$this->_expected]>";
    }
}
