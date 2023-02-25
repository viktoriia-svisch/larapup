<?php
namespace Mockery\Matcher;
class AnyArgs extends MatcherAbstract implements ArgumentListMatcher
{
    public function match(&$actual)
    {
        return true;
    }
    public function __toString()
    {
        return '<Any Arguments>';
    }
}
