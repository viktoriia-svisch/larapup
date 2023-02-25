<?php
namespace Mockery\Matcher;
class Subset extends MatcherAbstract
{
    private $expected;
    private $strict = true;
    public function __construct(array $expected, $strict = true)
    {
        $this->expected = $expected;
        $this->strict = $strict;
    }
    public static function strict(array $expected)
    {
        return new static($expected, true);
    }
    public static function loose(array $expected)
    {
        return new static($expected, false);
    }
    public function match(&$actual)
    {
        if (!is_array($actual)) {
            return false;
        }
        if ($this->strict) {
            return $actual === array_replace_recursive($actual, $this->expected);
        }
        return $actual == array_replace_recursive($actual, $this->expected);
    }
    public function __toString()
    {
        $return = '<Subset[';
        $elements = array();
        foreach ($this->expected as $k=>$v) {
            $elements[] = $k . '=' . (string) $v;
        }
        $return .= implode(', ', $elements) . ']>';
        return $return;
    }
}
