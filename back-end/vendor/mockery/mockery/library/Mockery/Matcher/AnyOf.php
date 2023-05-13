<?php
namespace Mockery\Matcher;
class AnyOf extends MatcherAbstract
{
    public function match(&$actual)
    {
        return in_array($actual, $this->_expected, true);
    }
    public function __toString()
    {
        return '<AnyOf>';
    }
}
