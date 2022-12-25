<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
abstract class StatelessInvocation implements Invocation
{
    public function invoked(BaseInvocation $invocation)
    {
    }
    public function verify()
    {
    }
}
