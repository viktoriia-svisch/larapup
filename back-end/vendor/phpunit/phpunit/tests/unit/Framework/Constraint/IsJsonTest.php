<?php
namespace PHPUnit\Framework\Constraint;
class IsJsonTest extends ConstraintTestCase
{
    public static function evaluateDataprovider(): array
    {
        return [
            'valid JSON'                                     => [true, '{}'],
            'empty string should be treated as invalid JSON' => [false, ''],
        ];
    }
    public function testEvaluate($expected, $jsonOther): void
    {
        $constraint = new IsJson;
        $this->assertEquals($expected, $constraint->evaluate($jsonOther, '', true));
    }
}
