<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
class AnyParameters extends StatelessInvocation
{
    public function toString(): string
    {
        return 'with any parameters';
    }
    public function matches(BaseInvocation $invocation)
    {
        return true;
    }
}
