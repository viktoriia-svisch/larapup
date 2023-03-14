<?php
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Mockery\Matcher\PHPUnitConstraint;
class PHPUnitConstraintTest extends MockeryTestCase
{
    protected $matcher;
    protected $rethrowingMatcher;
    protected $constraint;
    public function mockeryTestSetUp()
    {
        if (class_exists('\PHPUnit\Framework\AssertionFailedError')) {
            $this->assertionFailedError = '\PHPUnit\Framework\AssertionFailedError';
            $this->frameworkConstraint = '\PHPUnit\Framework\Constraint';
        } else {
            $this->assertionFailedError = '\PHPUnit_Framework_AssertionFailedError';
            $this->frameworkConstraint = '\PHPUnit_Framework_Constraint';
        }
        $this->constraint = \Mockery::mock($this->frameworkConstraint);
        $this->matcher = new PHPUnitConstraint($this->constraint);
        $this->rethrowingMatcher = new PHPUnitConstraint($this->constraint, true);
    }
    public function testMatches()
    {
        $value1 = 'value1';
        $value2 = 'value1';
        $value3 = 'value1';
        $this->constraint
            ->shouldReceive('evaluate')
            ->once()
            ->with($value1)
            ->getMock()
            ->shouldReceive('evaluate')
            ->once()
            ->with($value2)
            ->andThrow($this->assertionFailedError)
            ->getMock()
            ->shouldReceive('evaluate')
            ->once()
            ->with($value3)
            ->getMock()
        ;
        $this->assertTrue($this->matcher->match($value1));
        $this->assertFalse($this->matcher->match($value2));
        $this->assertTrue($this->rethrowingMatcher->match($value3));
    }
    public function testMatchesWhereNotMatchAndRethrowing()
    {
        $this->expectException($this->assertionFailedError);
        $value = 'value';
        $this->constraint
            ->shouldReceive('evaluate')
            ->once()
            ->with($value)
            ->andThrow($this->assertionFailedError)
        ;
        $this->rethrowingMatcher->match($value);
    }
    public function test__toString()
    {
        $this->assertEquals('<Constraint>', $this->matcher);
    }
}
