<?php
use PHPUnit\Framework\TestCase;
class Issue1472Test extends TestCase
{
    public function testAssertEqualXMLStructure(): void
    {
        $doc = new DOMDocument;
        $doc->loadXML('<root><label>text content</label></root>');
        $xpath = new DOMXPath($doc);
        $labelElement = $doc->getElementsByTagName('label')->item(0);
        $this->assertEquals(1, $xpath->evaluate('count(
        $expectedElmt = $doc->createElement('label', 'text content');
        $this->assertEqualXMLStructure($expectedElmt, $labelElement);
        $this->assertEquals(1, $xpath->evaluate('count(
    }
}
