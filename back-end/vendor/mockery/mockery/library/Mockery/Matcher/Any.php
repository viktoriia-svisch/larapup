<?php
namespace Mockery\Matcher;
class Any extends MatcherAbstract
{
    public function match(&$actual)
    {
        return true;
    }
    public function __toString()
    {
        return '<Any>';
    }
}
