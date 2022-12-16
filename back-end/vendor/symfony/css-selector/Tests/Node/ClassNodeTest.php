<?php
namespace Symfony\Component\CssSelector\Tests\Node;
use Symfony\Component\CssSelector\Node\ClassNode;
use Symfony\Component\CssSelector\Node\ElementNode;
class ClassNodeTest extends AbstractNodeTest
{
    public function getToStringConversionTestData()
    {
        return [
            [new ClassNode(new ElementNode(), 'class'), 'Class[Element[*].class]'],
        ];
    }
    public function getSpecificityValueTestData()
    {
        return [
            [new ClassNode(new ElementNode(), 'class'), 10],
            [new ClassNode(new ElementNode(null, 'element'), 'class'), 11],
        ];
    }
}
