<?php
namespace PharIo\Manifest;
use DOMDocument;
class CopyrightElementTest extends \PHPUnit\Framework\TestCase {
    private $dom;
    private $copyright;
    protected function setUp() {
        $this->dom = new DOMDocument();
        $this->dom->loadXML('<?xml version="1.0" ?><copyright xmlns="https:
        $this->copyright = new CopyrightElement($this->dom->documentElement);
    }
    public function testThrowsExceptionWhenGetAuthroElementsIsCalledButNodesAreMissing() {
        $this->expectException(ManifestElementException::class);
        $this->copyright->getAuthorElements();
    }
    public function testThrowsExceptionWhenGetLicenseElementIsCalledButNodeIsMissing() {
        $this->expectException(ManifestElementException::class);
        $this->copyright->getLicenseElement();
    }
    public function testGetAuthorElementsReturnsAuthorElementCollection() {
        $this->dom->documentElement->appendChild(
            $this->dom->createElementNS('https:
        );
        $this->assertInstanceOf(
            AuthorElementCollection::class, $this->copyright->getAuthorElements()
        );
    }
    public function testGetLicenseElementReturnsLicenseElement() {
        $this->dom->documentElement->appendChild(
            $this->dom->createElementNS('https:
        );
        $this->assertInstanceOf(
            LicenseElement::class, $this->copyright->getLicenseElement()
        );
    }
}
