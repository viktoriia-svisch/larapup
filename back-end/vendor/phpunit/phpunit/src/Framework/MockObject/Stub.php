<?php
namespace PHPUnit\Framework\MockObject;
use PHPUnit\Framework\SelfDescribing;
interface Stub extends SelfDescribing
{
    public function invoke(Invocation $invocation);
}
