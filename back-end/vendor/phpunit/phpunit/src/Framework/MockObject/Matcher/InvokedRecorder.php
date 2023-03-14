<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
abstract class InvokedRecorder implements Invocation
{
    private $invocations = [];
    public function getInvocationCount()
    {
        return \count($this->invocations);
    }
    public function getInvocations()
    {
        return $this->invocations;
    }
    public function hasBeenInvoked()
    {
        return \count($this->invocations) > 0;
    }
    public function invoked(BaseInvocation $invocation): void
    {
        $this->invocations[] = $invocation;
    }
    public function matches(BaseInvocation $invocation)
    {
        return true;
    }
}
