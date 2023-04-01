<?php
namespace Mockery\Matcher;
class NoArgs extends MatcherAbstract implements ArgumentListMatcher
{
    public function match(&$actual)
    {
        return count($actual) == 0;
    }
    public function __toString()
    {
        return '<No Arguments>';
    }
}
