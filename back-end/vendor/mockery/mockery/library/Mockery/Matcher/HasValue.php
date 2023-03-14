<?php
namespace Mockery\Matcher;
class HasValue extends MatcherAbstract
{
    public function match(&$actual)
    {
        return in_array($this->_expected, $actual);
    }
    public function __toString()
    {
        $return = '<HasValue[' . (string) $this->_expected . ']>';
        return $return;
    }
}
