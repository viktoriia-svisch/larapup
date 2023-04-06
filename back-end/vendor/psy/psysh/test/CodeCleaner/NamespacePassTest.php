<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner;
use Psy\CodeCleaner\NamespacePass;
class NamespacePassTest extends CodeCleanerTestCase
{
    private $cleaner;
    public function setUp()
    {
        $this->cleaner = new CodeCleaner();
        $this->setPass(new NamespacePass($this->cleaner));
    }
    public function testProcess()
    {
        $this->parseAndTraverse('');
        $this->assertNull($this->cleaner->getNamespace());
        $this->parseAndTraverse('array_merge()');
        $this->assertNull($this->cleaner->getNamespace());
        $this->parseAndTraverse('namespace Alpha');
        $this->assertSame(['Alpha'], $this->cleaner->getNamespace());
        $this->parseAndTraverse('namespace Beta; class B {}');
        $this->assertSame(['Beta'], $this->cleaner->getNamespace());
        $this->parseAndTraverse('namespace Gamma { array_merge(); }');
        if (\defined('PhpParser\\Node\\Stmt\\Namespace_::KIND_SEMICOLON')) {
            $this->assertNull($this->cleaner->getNamespace());
        } else {
            $this->assertSame(['Gamma'], $this->cleaner->getNamespace());
        }
        $this->parseAndTraverse('namespace Delta');
        $this->parseAndTraverse('namespace { array_merge(); }');
        $this->assertNull($this->cleaner->getNamespace());
    }
}
