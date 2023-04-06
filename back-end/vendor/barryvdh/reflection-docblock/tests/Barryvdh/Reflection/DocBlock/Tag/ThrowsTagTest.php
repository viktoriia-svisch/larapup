<?php
namespace Barryvdh\Reflection\DocBlock\Tag;
class ThrowsTagTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorParsesInputsIntoCorrectFields(
        $type,
        $content,
        $extractedType,
        $extractedTypes,
        $extractedDescription
    ) {
        $tag = new ThrowsTag($type, $content);
        $this->assertEquals($type, $tag->getName());
        $this->assertEquals($extractedType, $tag->getType());
        $this->assertEquals($extractedTypes, $tag->getTypes());
        $this->assertEquals($extractedDescription, $tag->getDescription());
    }
    public function provideDataForConstructor()
    {
        return array(
            array('throws', '', '', array(), ''),
            array('throws', 'int', 'int', array('int'), ''),
            array(
                'throws',
                'int Number of Bobs',
                'int',
                array('int'),
                'Number of Bobs'
            ),
            array(
                'throws',
                'int|double Number of Bobs',
                'int|double',
                array('int', 'double'),
                'Number of Bobs'
            ),
            array(
                'throws',
                "int Number of \n Bobs",
                'int',
                array('int'),
                "Number of \n Bobs"
            ),
            array(
                'throws',
                " int Number of Bobs",
                'int',
                array('int'),
                "Number of Bobs"
            ),
            array(
                'throws',
                "int\nNumber of Bobs",
                'int',
                array('int'),
                "Number of Bobs"
            )
        );
    }
}
