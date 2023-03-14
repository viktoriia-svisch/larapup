<?php
namespace Hamcrest\Text;
use Hamcrest\Description;
use Hamcrest\TypeSafeMatcher;
class StringContainsInOrder extends TypeSafeMatcher
{
    private $_substrings;
    public function __construct(array $substrings)
    {
        parent::__construct(self::TYPE_STRING);
        $this->_substrings = $substrings;
    }
    protected function matchesSafely($item)
    {
        $fromIndex = 0;
        foreach ($this->_substrings as $substring) {
            if (false === $fromIndex = strpos($item, $substring, $fromIndex)) {
                return false;
            }
        }
        return true;
    }
    protected function describeMismatchSafely($item, Description $mismatchDescription)
    {
        $mismatchDescription->appendText('was ')->appendText($item);
    }
    public function describeTo(Description $description)
    {
        $description->appendText('a string containing ')
                                ->appendValueList('', ', ', '', $this->_substrings)
                                ->appendText(' in order')
                                ;
    }
    public static function stringContainsInOrder()
    {
        $args = func_get_args();
        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }
        return new self($args);
    }
}
