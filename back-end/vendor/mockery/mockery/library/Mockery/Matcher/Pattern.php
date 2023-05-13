<?php
namespace Mockery\Matcher;
class Pattern extends MatcherAbstract
{
    public function match(&$actual)
    {
        return preg_match($this->_expected, (string) $actual) >= 1;
    }
    public function __toString()
    {
        return '<Pattern>';
    }
}
