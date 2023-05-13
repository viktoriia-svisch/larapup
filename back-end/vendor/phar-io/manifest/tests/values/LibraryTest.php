<?php
namespace PharIo\Manifest;
use PHPUnit\Framework\TestCase;
class LibraryTest extends TestCase {
    private $type;
    protected function setUp() {
        $this->type = Type::library();
    }
    public function testCanBeCreated() {
        $this->assertInstanceOf(Library::class, $this->type);
    }
    public function testIsNotApplication() {
        $this->assertFalse($this->type->isApplication());
    }
    public function testIsLibrary() {
        $this->assertTrue($this->type->isLibrary());
    }
    public function testIsNotExtension() {
        $this->assertFalse($this->type->isExtension());
    }
}
