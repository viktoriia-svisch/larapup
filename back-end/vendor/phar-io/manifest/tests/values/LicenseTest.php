<?php
namespace PharIo\Manifest;
use PHPUnit\Framework\TestCase;
class LicenseTest extends TestCase {
    private $license;
    protected function setUp() {
        $this->license = new License('BSD-3-Clause', new Url('https:
    }
    public function testCanBeCreated() {
        $this->assertInstanceOf(License::class, $this->license);
    }
    public function testNameCanBeRetrieved() {
        $this->assertEquals('BSD-3-Clause', $this->license->getName());
    }
    public function testUrlCanBeRetrieved() {
        $this->assertEquals('https:
    }
}
