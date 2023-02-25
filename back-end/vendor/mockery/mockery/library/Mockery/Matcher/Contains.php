<?php
namespace Mockery\Matcher;
class Contains extends MatcherAbstract
{
    public function match(&$actual)
    {
        $values = array_values($actual);
        foreach ($this->_expected as $exp) {
            $match = false;
            foreach ($values as $val) {
                if ($exp === $val || $exp == $val) {
                    $match = true;
                    break;
                }
            }
            if ($match === false) {
                return false;
            }
        }
        return true;
    }
    public function __toString()
    {
        $return = '<Contains[';
        $elements = array();
        foreach ($this->_expected as $v) {
            $elements[] = (string) $v;
        }
        $return .= implode(', ', $elements) . ']>';
        return $return;
    }
}
