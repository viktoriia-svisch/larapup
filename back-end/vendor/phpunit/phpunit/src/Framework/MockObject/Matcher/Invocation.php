<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use PHPUnit\Framework\MockObject\Verifiable;
use PHPUnit\Framework\SelfDescribing;
interface Invocation extends SelfDescribing, Verifiable
{
    public function invoked(BaseInvocation $invocation);
    public function matches(BaseInvocation $invocation);
}
