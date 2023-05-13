<?php
namespace PharIo\Manifest;
class ComponentElementTest extends \PHPUnit\Framework\TestCase {
    private $component;
    protected function setUp() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><component xmlns="https:
        $this->component = new ComponentElement($dom->documentElement);
    }
    public function testNameCanBeRetrieved() {
        $this->assertEquals('phar-io/phive', $this->component->getName());
    }
    public function testEmailCanBeRetrieved() {
        $this->assertEquals('0.6.0', $this->component->getVersion());
    }
}
