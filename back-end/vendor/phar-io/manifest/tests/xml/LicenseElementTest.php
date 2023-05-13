<?php
namespace PharIo\Manifest;
class LicenseElementTest extends \PHPUnit\Framework\TestCase {
    private $license;
    protected function setUp() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><license xmlns="https:
        $this->license = new LicenseElement($dom->documentElement);
    }
    public function testTypeCanBeRetrieved() {
        $this->assertEquals('BSD-3', $this->license->getType());
    }
    public function testUrlCanBeRetrieved() {
        $this->assertEquals('https:
    }
}
