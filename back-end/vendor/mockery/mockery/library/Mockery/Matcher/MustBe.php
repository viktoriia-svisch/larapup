<?php
namespace Mockery\Matcher;
class MustBe extends MatcherAbstract
{
    public function match(&$actual)
    {
        if (!is_object($actual)) {
            return $this->_expected === $actual;
        }
        return $this->_expected == $actual;
    }
    public function __toString()
    {
        return '<MustBe>';
    }
}
