<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\AbstractClassPass;
class AbstractClassPassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new AbstractClassPass());
    }
    public function testProcessStatementFails($code)
    {
        $this->parseAndTraverse($code);
    }
    public function invalidStatements()
    {
        return [
            ['class A { abstract function a(); }'],
            ['abstract class B { abstract function b() {} }'],
            ['abstract class B { abstract function b() { echo "yep"; } }'],
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
            ['abstract class C { function c() {} }'],
            ['abstract class D { abstract function d(); }'],
        ];
    }
}
