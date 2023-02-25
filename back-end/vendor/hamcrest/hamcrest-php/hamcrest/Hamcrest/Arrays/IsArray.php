<?php
namespace Hamcrest\Arrays;
use Hamcrest\Description;
use Hamcrest\TypeSafeMatcher;
use Hamcrest\Util;
class IsArray extends TypeSafeMatcher
{
    private $_elementMatchers;
    public function __construct(array $elementMatchers)
    {
        parent::__construct(self::TYPE_ARRAY);
        Util::checkAllAreMatchers($elementMatchers);
        $this->_elementMatchers = $elementMatchers;
    }
    protected function matchesSafely($array)
    {
        if (array_keys($array) != array_keys($this->_elementMatchers)) {
            return false;
        }
        foreach ($this->_elementMatchers as $k => $matcher) {
            if (!$matcher->matches($array[$k])) {
                return false;
            }
        }
        return true;
    }
    protected function describeMismatchSafely($actual, Description $mismatchDescription)
    {
        if (count($actual) != count($this->_elementMatchers)) {
            $mismatchDescription->appendText('array length was ' . count($actual));
            return;
        } elseif (array_keys($actual) != array_keys($this->_elementMatchers)) {
            $mismatchDescription->appendText('array keys were ')
                                                    ->appendValueList(
                                                        $this->descriptionStart(),
                                                        $this->descriptionSeparator(),
                                                        $this->descriptionEnd(),
                                                        array_keys($actual)
                                                    )
                                                    ;
            return;
        }
        foreach ($this->_elementMatchers as $k => $matcher) {
            if (!$matcher->matches($actual[$k])) {
                $mismatchDescription->appendText('element ')->appendValue($k)
                    ->appendText(' was ')->appendValue($actual[$k]);
                return;
            }
        }
    }
    public function describeTo(Description $description)
    {
        $description->appendList(
            $this->descriptionStart(),
            $this->descriptionSeparator(),
            $this->descriptionEnd(),
            $this->_elementMatchers
        );
    }
    public static function anArray()
    {
        $args = func_get_args();
        return new self(Util::createMatcherArray($args));
    }
    protected function descriptionStart()
    {
        return '[';
    }
    protected function descriptionSeparator()
    {
        return ', ';
    }
    protected function descriptionEnd()
    {
        return ']';
    }
}
