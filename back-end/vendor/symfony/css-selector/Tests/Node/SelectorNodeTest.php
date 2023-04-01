<?php
namespace Symfony\Component\CssSelector\Tests\Node;
use Symfony\Component\CssSelector\Node\ElementNode;
use Symfony\Component\CssSelector\Node\SelectorNode;
class SelectorNodeTest extends AbstractNodeTest
{
    public function getToStringConversionTestData()
    {
        return [
            [new SelectorNode(new ElementNode()), 'Selector[Element[*]]'],
            [new SelectorNode(new ElementNode(), 'pseudo'), 'Selector[Element[*]::pseudo]'],
        ];
    }
    public function getSpecificityValueTestData()
    {
        return [
            [new SelectorNode(new ElementNode()), 0],
            [new SelectorNode(new ElementNode(), 'pseudo'), 1],
        ];
    }
}
