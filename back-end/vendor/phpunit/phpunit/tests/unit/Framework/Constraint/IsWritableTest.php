<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
class IsWritableTest extends ConstraintTestCase
{
    public function testConstraintIsWritable(): void
    {
        $constraint = new IsWritable;
        $this->assertFalse($constraint->evaluate('foo', '', true));
        $this->assertEquals('is writable', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('foo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that "foo" is writable.
EOF
                ,
                TestFailure::exceptionToString($e)
            );
            return;
        }
        $this->fail();
    }
}
