<?php
namespace Mockery\Matcher;
class Type extends MatcherAbstract
{
    public function match(&$actual)
    {
        $function = 'is_' . strtolower($this->_expected);
        if (function_exists($function)) {
            return $function($actual);
        } elseif (is_string($this->_expected)
        && (class_exists($this->_expected) || interface_exists($this->_expected))) {
            return $actual instanceof $this->_expected;
        }
        return false;
    }
    public function __toString()
    {
        return '<' . ucfirst($this->_expected) . '>';
    }
}
