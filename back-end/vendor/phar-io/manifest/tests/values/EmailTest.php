<?php
namespace PharIo\Manifest;
use PHPUnit\Framework\TestCase;
class EmailTest extends TestCase {
    public function testCanBeCreatedForValidEmail() {
        $this->assertInstanceOf(Email::class, new Email('user@example.com'));
    }
    public function testCanBeUsedAsString() {
        $this->assertEquals('user@example.com', new Email('user@example.com'));
    }
    public function testCannotBeCreatedForInvalidEmail() {
        $this->expectException(InvalidEmailException::class);
        new Email('invalid');
    }
}
