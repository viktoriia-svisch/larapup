<?php
namespace Symfony\Component\CssSelector\Tests\Parser\Shortcut;
use PHPUnit\Framework\TestCase;
use Symfony\Component\CssSelector\Node\SelectorNode;
use Symfony\Component\CssSelector\Parser\Shortcut\HashParser;
class HashParserTest extends TestCase
{
    public function testParse($source, $representation)
    {
        $parser = new HashParser();
        $selectors = $parser->parse($source);
        $this->assertCount(1, $selectors);
        $selector = $selectors[0];
        $this->assertEquals($representation, (string) $selector->getTree());
    }
    public function getParseTestData()
    {
        return [
            ['#testid', 'Hash[Element[*]#testid]'],
            ['testel#testid', 'Hash[Element[testel]#testid]'],
            ['testns|#testid', 'Hash[Element[testns|*]#testid]'],
            ['testns|*#testid', 'Hash[Element[testns|*]#testid]'],
            ['testns|testel#testid', 'Hash[Element[testns|testel]#testid]'],
        ];
    }
}
