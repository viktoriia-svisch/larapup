<?php
namespace Symfony\Component\CssSelector\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\CssSelector\CssSelectorConverter;
class CssSelectorConverterTest extends TestCase
{
    public function testCssToXPath()
    {
        $converter = new CssSelectorConverter();
        $this->assertEquals('descendant-or-self::*', $converter->toXPath(''));
        $this->assertEquals('descendant-or-self::h1', $converter->toXPath('h1'));
        $this->assertEquals("descendant-or-self::h1[@id = 'foo']", $converter->toXPath('h1#foo'));
        $this->assertEquals("descendant-or-self::h1[@class and contains(concat(' ', normalize-space(@class), ' '), ' foo ')]", $converter->toXPath('h1.foo'));
        $this->assertEquals('descendant-or-self::foo:h1', $converter->toXPath('foo|h1'));
        $this->assertEquals('descendant-or-self::h1', $converter->toXPath('H1'));
    }
    public function testCssToXPathXml()
    {
        $converter = new CssSelectorConverter(false);
        $this->assertEquals('descendant-or-self::H1', $converter->toXPath('H1'));
    }
    public function testParseExceptions()
    {
        $converter = new CssSelectorConverter();
        $converter->toXPath('h1:');
    }
    public function testCssToXPathWithoutPrefix($css, $xpath)
    {
        $converter = new CssSelectorConverter();
        $this->assertEquals($xpath, $converter->toXPath($css, ''), '->parse() parses an input string and returns a node');
    }
    public function getCssToXPathWithoutPrefixTestData()
    {
        return [
            ['h1', 'h1'],
            ['foo|h1', 'foo:h1'],
            ['h1, h2, h3', 'h1 | h2 | h3'],
            ['h1:nth-child(3n+1)', "**[@class and contains(concat(' ', normalize-space(@class), ' '), ' foo ')]"],
            ['h1 #foo', "h1/descendant-or-self::**[@class and contains(@class, 'foo')]"],
            ['div>.foo', "div/*[@class and contains(concat(' ', normalize-space(@class), ' '), ' foo ')]"],
            ['div > .foo', "div/*[@class and contains(concat(' ', normalize-space(@class), ' '), ' foo ')]"],
        ];
    }
}
