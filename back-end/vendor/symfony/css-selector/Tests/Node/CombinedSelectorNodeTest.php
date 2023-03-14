<?php
namespace Symfony\Component\CssSelector\Tests\Node;
use Symfony\Component\CssSelector\Node\CombinedSelectorNode;
use Symfony\Component\CssSelector\Node\ElementNode;
class CombinedSelectorNodeTest extends AbstractNodeTest
{
    public function getToStringConversionTestData()
    {
        return [
            [new CombinedSelectorNode(new ElementNode(), '>', new ElementNode()), 'CombinedSelector[Element[*] > Element[*]]'],
            [new CombinedSelectorNode(new ElementNode(), ' ', new ElementNode()), 'CombinedSelector[Element[*] <followed> Element[*]]'],
        ];
    }
    public function getSpecificityValueTestData()
    {
        return [
            [new CombinedSelectorNode(new ElementNode(), '>', new ElementNode()), 0],
            [new CombinedSelectorNode(new ElementNode(null, 'element'), '>', new ElementNode()), 1],
            [new CombinedSelectorNode(new ElementNode(null, 'element'), '>', new ElementNode(null, 'element')), 2],
        ];
    }
}
