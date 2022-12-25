<?php
namespace Symfony\Component\CssSelector\Tests\Parser\Shortcut;
use PHPUnit\Framework\TestCase;
use Symfony\Component\CssSelector\Node\SelectorNode;
use Symfony\Component\CssSelector\Parser\Shortcut\ClassParser;
class ClassParserTest extends TestCase
{
    public function testParse($source, $representation)
    {
        $parser = new ClassParser();
        $selectors = $parser->parse($source);
        $this->assertCount(1, $selectors);
        $selector = $selectors[0];
        $this->assertEquals($representation, (string) $selector->getTree());
    }
    public function getParseTestData()
    {
        return [
            ['.testclass', 'Class[Element[*].testclass]'],
            ['testel.testclass', 'Class[Element[testel].testclass]'],
            ['testns|.testclass', 'Class[Element[testns|*].testclass]'],
            ['testns|*.testclass', 'Class[Element[testns|*].testclass]'],
            ['testns|testel.testclass', 'Class[Element[testns|testel].testclass]'],
        ];
    }
}
