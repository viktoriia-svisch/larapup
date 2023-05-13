<?php
namespace Symfony\Component\CssSelector\Tests\Node;
use Symfony\Component\CssSelector\Node\ElementNode;
use Symfony\Component\CssSelector\Node\HashNode;
class HashNodeTest extends AbstractNodeTest
{
    public function getToStringConversionTestData()
    {
        return [
            [new HashNode(new ElementNode(), 'id'), 'Hash[Element[*]#id]'],
        ];
    }
    public function getSpecificityValueTestData()
    {
        return [
            [new HashNode(new ElementNode(), 'id'), 100],
            [new HashNode(new ElementNode(null, 'id'), 'class'), 101],
        ];
    }
}
