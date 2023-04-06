<?php
namespace Mockery\Matcher;
class Not extends MatcherAbstract
{
    public function match(&$actual)
    {
        return $actual !== $this->_expected;
    }
    public function __toString()
    {
        return '<Not>';
    }
}
