<?php
namespace Mockery\Matcher;
class NotAnyOf extends MatcherAbstract
{
    public function match(&$actual)
    {
        foreach ($this->_expected as $exp) {
            if ($actual === $exp || $actual == $exp) {
                return false;
            }
        }
        return true;
    }
    public function __toString()
    {
        return '<AnyOf>';
    }
}
