<?php
namespace PharIo\Manifest;
use PHPUnit\Framework\TestCase;
class UrlTest extends TestCase {
    public function testCanBeCreatedForValidUrl() {
        $this->assertInstanceOf(Url::class, new Url('https:
    }
    public function testCanBeUsedAsString() {
        $this->assertEquals('https:
    }
    public function testCannotBeCreatedForInvalidUrl() {
        $this->expectException(InvalidUrlException::class);
        new Url('invalid');
    }
}
