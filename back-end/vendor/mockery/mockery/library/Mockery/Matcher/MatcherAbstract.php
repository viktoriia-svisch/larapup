<?php
namespace Mockery\Matcher;
abstract class MatcherAbstract
{
    protected $_expected = null;
    public function __construct($expected = null)
    {
        $this->_expected = $expected;
    }
    abstract public function match(&$actual);
    abstract public function __toString();
}
