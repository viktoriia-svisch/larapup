<?php
namespace Hamcrest;
abstract class BaseMatcher implements Matcher
{
    public function describeMismatch($item, Description $description)
    {
        $description->appendText('was ')->appendValue($item);
    }
    public function __toString()
    {
        return StringDescription::toString($this);
    }
}
