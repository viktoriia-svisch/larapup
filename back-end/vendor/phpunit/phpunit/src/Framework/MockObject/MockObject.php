<?php
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
interface PHPUnit_Framework_MockObject_MockObject 
{
    public function __phpunit_setOriginalObject($originalObject);
    public function __phpunit_getInvocationMocker();
    public function __phpunit_verify(bool $unsetInvocationMocker = true);
    public function __phpunit_hasMatchers();
    public function __phpunit_setReturnValueGeneration(bool $returnValueGeneration);
    public function expects(Invocation $matcher);
}
