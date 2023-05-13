<?php
namespace Mockery\Matcher;
class AndAnyOtherArgs extends MatcherAbstract
{
    public function match(&$actual)
    {
        return true;
    }
    public function __toString()
    {
        return '<AndAnyOthers>';
    }
}
