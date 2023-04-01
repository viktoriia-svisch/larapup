<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\FinalClassPass;
class FinalClassPassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new FinalClassPass());
    }
    public function testProcessStatementFails($code)
    {
        $this->parseAndTraverse($code);
    }
    public function invalidStatements()
    {
        $data = [
            ['final class A {} class B extends A {}'],
            ['class A {} final class B extends A {} class C extends B {}'],
        ];
        if (!\defined('HHVM_VERSION')) {
            $data[] = ['class A extends \\Closure {}'];
        }
        return $data;
    }
    public function testProcessStatementPasses($code)
    {
        $this->parseAndTraverse($code);
        $this->assertTrue(true);
    }
    public function validStatements()
    {
        return [
            ['class A extends \\stdClass {}'],
            ['final class A extends \\stdClass {}'],
            ['class A {} class B extends A {}'],
        ];
    }
}
