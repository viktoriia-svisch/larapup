<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
class IsNullTest extends ConstraintTestCase
{
    public function testConstraintIsNull(): void
    {
        $constraint = new IsNull;
        $this->assertFalse($constraint->evaluate(0, '', true));
        $this->assertTrue($constraint->evaluate(null, '', true));
        $this->assertEquals('is null', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(0);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 0 is null.
EOF
                ,
                TestFailure::exceptionToString($e)
            );
            return;
        }
        $this->fail();
    }
    public function testConstraintIsNull2(): void
    {
        $constraint = new IsNull;
        try {
            $constraint->evaluate(0, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 0 is null.
EOF
                ,
                TestFailure::exceptionToString($e)
            );
            return;
        }
        $this->fail();
    }
}
