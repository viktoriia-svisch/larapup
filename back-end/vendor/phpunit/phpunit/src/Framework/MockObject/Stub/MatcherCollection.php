<?php
namespace PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
interface MatcherCollection
{
    public function addMatcher(Invocation $matcher);
}
