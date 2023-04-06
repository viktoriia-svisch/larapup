<?php
namespace Symfony\Component\CssSelector\Tests\Node;
use PHPUnit\Framework\TestCase;
use Symfony\Component\CssSelector\Node\NodeInterface;
abstract class AbstractNodeTest extends TestCase
{
    public function testToStringConversion(NodeInterface $node, $representation)
    {
        $this->assertEquals($representation, (string) $node);
    }
    public function testSpecificityValue(NodeInterface $node, $value)
    {
        $this->assertEquals($value, $node->getSpecificity()->getValue());
    }
    abstract public function getToStringConversionTestData();
    abstract public function getSpecificityValueTestData();
}
