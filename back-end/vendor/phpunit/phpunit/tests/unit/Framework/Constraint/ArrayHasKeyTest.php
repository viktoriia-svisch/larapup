<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
class ArrayHasKeyTest extends ConstraintTestCase
{
    public function testConstraintArrayHasKey(): void
    {
        $constraint = new ArrayHasKey(0);
        $this->assertFalse($constraint->evaluate([], '', true));
        $this->assertEquals('has the key 0', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate([]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array has the key 0.
EOF
                ,
                TestFailure::exceptionToString($e)
            );
            return;
        }
        $this->fail();
    }
    public function testConstraintArrayHasKey2(): void
    {
        $constraint = new ArrayHasKey(0);
        try {
            $constraint->evaluate([], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message\nFailed asserting that an array has the key 0.
EOF
                ,
                TestFailure::exceptionToString($e)
            );
            return;
        }
        $this->fail();
    }
}
