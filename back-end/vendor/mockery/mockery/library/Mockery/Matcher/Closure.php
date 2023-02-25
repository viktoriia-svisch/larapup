<?php
namespace Mockery\Matcher;
class Closure extends MatcherAbstract
{
    public function match(&$actual)
    {
        $closure = $this->_expected;
        $result = $closure($actual);
        return $result === true;
    }
    public function __toString()
    {
        return '<Closure===true>';
    }
}
