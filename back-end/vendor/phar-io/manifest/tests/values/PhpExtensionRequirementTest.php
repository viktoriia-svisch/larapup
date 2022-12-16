<?php
namespace PharIo\Manifest;
use PHPUnit\Framework\TestCase;
class PhpExtensionRequirementTest extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf(PhpExtensionRequirement::class, new PhpExtensionRequirement('dom'));
    }
    public function testCanBeUsedAsString() {
        $this->assertEquals('dom', new PhpExtensionRequirement('dom'));
    }
}
