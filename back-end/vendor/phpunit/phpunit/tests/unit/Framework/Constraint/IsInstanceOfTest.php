<?php declare(strict_types=1);
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
final class IsInstanceOfTest extends ConstraintTestCase
{
    public function testConstraintInstanceOf(): void
    {
        $constraint = new IsInstanceOf(\stdClass::class);
        self::assertTrue($constraint->evaluate(new \stdClass, '', true));
    }
    public function testConstraintFailsOnString(): void
    {
        $constraint = new IsInstanceOf(\stdClass::class);
        try {
            $constraint->evaluate('stdClass');
        } catch (ExpectationFailedException $e) {
            self::assertSame(
                <<<EOT
Failed asserting that 'stdClass' is an instance of class "stdClass".
EOT
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }
}
