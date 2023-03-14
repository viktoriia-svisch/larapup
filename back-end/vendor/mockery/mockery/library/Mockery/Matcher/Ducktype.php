<?php
namespace Mockery\Matcher;
class Ducktype extends MatcherAbstract
{
    public function match(&$actual)
    {
        if (!is_object($actual)) {
            return false;
        }
        foreach ($this->_expected as $method) {
            if (!method_exists($actual, $method)) {
                return false;
            }
        }
        return true;
    }
    public function __toString()
    {
        return '<Ducktype[' . implode(', ', $this->_expected) . ']>';
    }
}
