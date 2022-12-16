<?php
namespace Symfony\Component\CssSelector\Tests\Parser\Shortcut;
use PHPUnit\Framework\TestCase;
use Symfony\Component\CssSelector\Node\SelectorNode;
use Symfony\Component\CssSelector\Parser\Shortcut\EmptyStringParser;
class EmptyStringParserTest extends TestCase
{
    public function testParse()
    {
        $parser = new EmptyStringParser();
        $selectors = $parser->parse('');
        $this->assertCount(1, $selectors);
        $selector = $selectors[0];
        $this->assertEquals('Element[*]', (string) $selector->getTree());
        $selectors = $parser->parse('this will produce an empty array');
        $this->assertCount(0, $selectors);
    }
}
