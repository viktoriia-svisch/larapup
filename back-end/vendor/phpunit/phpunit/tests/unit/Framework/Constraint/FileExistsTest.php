<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
class FileExistsTest extends ConstraintTestCase
{
    public function testConstraintFileExists(): void
    {
        $constraint = new FileExists;
        $this->assertFalse($constraint->evaluate('foo', '', true));
        $this->assertEquals('file exists', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate('foo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that file "foo" exists.
EOF
                ,
                TestFailure::exceptionToString($e)
            );
            return;
        }
        $this->fail();
    }
    public function testConstraintFileExists2(): void
    {
        $constraint = new FileExists;
        try {
            $constraint->evaluate('foo', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that file "foo" exists.
EOF
                ,
                TestFailure::exceptionToString($e)
            );
            return;
        }
        $this->fail();
    }
}
