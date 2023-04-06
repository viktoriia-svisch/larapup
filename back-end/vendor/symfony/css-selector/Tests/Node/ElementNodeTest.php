<?php
namespace Symfony\Component\CssSelector\Tests\Node;
use Symfony\Component\CssSelector\Node\ElementNode;
class ElementNodeTest extends AbstractNodeTest
{
    public function getToStringConversionTestData()
    {
        return [
            [new ElementNode(), 'Element[*]'],
            [new ElementNode(null, 'element'), 'Element[element]'],
            [new ElementNode('namespace', 'element'), 'Element[namespace|element]'],
        ];
    }
    public function getSpecificityValueTestData()
    {
        return [
            [new ElementNode(), 0],
            [new ElementNode(null, 'element'), 1],
            [new ElementNode('namespace', 'element'), 1],
        ];
    }
}
