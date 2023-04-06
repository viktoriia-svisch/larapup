<?php
namespace Mockery\Matcher;
class MultiArgumentClosure extends MatcherAbstract implements ArgumentListMatcher
{
    public function match(&$actual)
    {
        $closure = $this->_expected;
        return true === call_user_func_array($closure, $actual);
    }
    public function __toString()
    {
        return '<MultiArgumentClosure===true>';
    }
}
