<?php
namespace PharIo\Manifest;
use PHPUnit\Framework\TestCase;
class ApplicationTest extends TestCase {
    private $type;
    protected function setUp() {
        $this->type = Type::application();
    }
    public function testCanBeCreated() {
        $this->assertInstanceOf(Application::class, $this->type);
    }
    public function testIsApplication() {
        $this->assertTrue($this->type->isApplication());
    }
    public function testIsNotLibrary() {
        $this->assertFalse($this->type->isLibrary());
    }
    public function testIsNotExtension() {
        $this->assertFalse($this->type->isExtension());
    }
}
