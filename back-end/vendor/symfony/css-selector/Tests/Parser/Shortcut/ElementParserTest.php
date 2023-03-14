<?php
namespace Symfony\Component\CssSelector\Tests\Parser\Shortcut;
use PHPUnit\Framework\TestCase;
use Symfony\Component\CssSelector\Node\SelectorNode;
use Symfony\Component\CssSelector\Parser\Shortcut\ElementParser;
class ElementParserTest extends TestCase
{
    public function testParse($source, $representation)
    {
        $parser = new ElementParser();
        $selectors = $parser->parse($source);
        $this->assertCount(1, $selectors);
        $selector = $selectors[0];
        $this->assertEquals($representation, (string) $selector->getTree());
    }
    public function getParseTestData()
    {
        return [
            ['*', 'Element[*]'],
            ['testel', 'Element[testel]'],
            ['testns|*', 'Element[testns|*]'],
            ['testns|testel', 'Element[testns|testel]'],
        ];
    }
}
