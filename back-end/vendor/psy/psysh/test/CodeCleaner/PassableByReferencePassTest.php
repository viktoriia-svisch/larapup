<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\PassableByReferencePass;
class PassableByReferencePassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new PassableByReferencePass());
    }
    public function testProcessStatementFails($code)
    {
        $this->parseAndTraverse($code);
    }
    public function invalidStatements()
    {
        return [
            ['array_pop(array())'],
            ['array_pop(array($foo))'],
            ['array_shift(array())'],
        ];
    }
    public function testProcessStatementPasses($code)
    {
        $this->parseAndTraverse($code);
        $this->assertTrue(true);
    }
    public function validStatements()
    {
        return [
            ['array_pop(json_decode("[]"))'],
            ['array_pop($foo)'],
            ['array_pop($foo->bar)'],
            ['array_pop($foo::baz)'],
            ['array_pop(Foo::qux)'],
        ];
    }
    public function testArrayMultisort($code)
    {
        $this->parseAndTraverse($code);
        $this->assertTrue(true);
    }
    public function validArrayMultisort()
    {
        return [
            ['array_multisort($a)'],
            ['array_multisort($a, $b)'],
            ['array_multisort($a, SORT_NATURAL, $b)'],
            ['array_multisort($a, SORT_NATURAL | SORT_FLAG_CASE, $b)'],
            ['array_multisort($a, SORT_ASC, SORT_NATURAL | SORT_FLAG_CASE, $b)'],
            ['array_multisort($a, SORT_NATURAL | SORT_FLAG_CASE, SORT_ASC, $b)'],
            ['array_multisort($a, $b, SORT_ASC, SORT_NATURAL | SORT_FLAG_CASE)'],
            ['array_multisort($a, SORT_NATURAL | SORT_FLAG_CASE, $b, SORT_ASC, SORT_NATURAL | SORT_FLAG_CASE)'],
            ['array_multisort($a, 1, $b)'],
            ['array_multisort($a, 1 + 2, $b)'],
            ['array_multisort($a, getMultisortFlags(), $b)'],
        ];
    }
    public function testInvalidArrayMultisort($code)
    {
        $this->parseAndTraverse($code);
    }
    public function invalidArrayMultisort()
    {
        return [
            ['array_multisort(1)'],
            ['array_multisort(array(1, 2, 3))'],
            ['array_multisort($a, SORT_NATURAL, SORT_ASC, SORT_NATURAL, $b)'],
        ];
    }
}
