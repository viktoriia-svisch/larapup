<?php
namespace Symfony\Component\CssSelector\Tests\Node;
use Symfony\Component\CssSelector\Node\ClassNode;
use Symfony\Component\CssSelector\Node\ElementNode;
use Symfony\Component\CssSelector\Node\NegationNode;
class NegationNodeTest extends AbstractNodeTest
{
    public function getToStringConversionTestData()
    {
        return [
            [new NegationNode(new ElementNode(), new ClassNode(new ElementNode(), 'class')), 'Negation[Element[*]:not(Class[Element[*].class])]'],
        ];
    }
    public function getSpecificityValueTestData()
    {
        return [
            [new NegationNode(new ElementNode(), new ClassNode(new ElementNode(), 'class')), 10],
        ];
    }
}
