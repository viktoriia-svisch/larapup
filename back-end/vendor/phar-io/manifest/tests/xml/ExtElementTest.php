<?php
namespace PharIo\Manifest;
class ExtElementTest extends \PHPUnit\Framework\TestCase {
    private $ext;
    protected function setUp() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><ext xmlns="https:
        $this->ext = new ExtElement($dom->documentElement);
    }
    public function testNameCanBeRetrieved() {
        $this->assertEquals('dom', $this->ext->getName());
    }
}
