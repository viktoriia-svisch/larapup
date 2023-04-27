<?php
namespace PHPUnit\Framework\MockObject;
interface Invokable extends Verifiable
{
    public function invoke(Invocation $invocation);
    public function matches(Invocation $invocation);
}
