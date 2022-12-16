<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
class IsTypeTest extends ConstraintTestCase
{
    public function testConstraintIsType(): void
    {
        $constraint = Assert::isType('string');
        $this->assertFalse($constraint->evaluate(0, '', true));
        $this->assertTrue($constraint->evaluate('', '', true));
        $this->assertEquals('is of type "string"', $constraint->toString());
        $this->assertCount(1, $constraint);
        try {
            $constraint->evaluate(new \stdClass);
        } catch (ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(
                <<<EOF
Failed asserting that stdClass Object &%x () is of type "string".
EOF
                ,
                $this->trimnl(TestFailure::exceptionToString($e))
            );
            return;
        }
        $this->fail();
    }
    public function testConstraintIsType2(): void
    {
        $constraint = Assert::isType('string');
        try {
            $constraint->evaluate(new \stdClass, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(
                <<<EOF
custom message
Failed asserting that stdClass Object &%x () is of type "string".
EOF
                ,
                $this->trimnl(TestFailure::exceptionToString($e))
            );
            return;
        }
        $this->fail();
    }
    public function testConstraintIsResourceTypeEvaluatesCorrectlyWithResources($resource): void
    {
        $constraint = Assert::isType('resource');
        $this->assertTrue($constraint->evaluate($resource, '', true));
        @\fclose($resource);
    }
    public function resources()
    {
        $fh = \fopen(__FILE__, 'r');
        \fclose($fh);
        return [
            'open resource'     => [\fopen(__FILE__, 'r')],
            'closed resource'   => [$fh],
        ];
    }
    public function testIterableTypeIsSupported(): void
    {
        $constraint = Assert::isType('iterable');
        $this->assertFalse($constraint->evaluate('', '', true));
        $this->assertTrue($constraint->evaluate([], '', true));
        $this->assertEquals('is of type "iterable"', $constraint->toString());
    }
    private function trimnl($string)
    {
        return \preg_replace('/[ ]*\n/', "\n", $string);
    }
}
