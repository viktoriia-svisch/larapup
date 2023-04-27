<?php
namespace Barryvdh\Reflection\DocBlock\Tag;
class ReturnTagTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorParsesInputsIntoCorrectFields(
        $type,
        $content,
        $extractedType,
        $extractedTypes,
        $extractedDescription
    ) {
        $tag = new ReturnTag($type, $content);
        $this->assertEquals($type, $tag->getName());
        $this->assertEquals($extractedType, $tag->getType());
        $this->assertEquals($extractedTypes, $tag->getTypes());
        $this->assertEquals($extractedDescription, $tag->getDescription());
    }
    public function provideDataForConstructor()
    {
        return array(
            array('return', '', '', array(), ''),
            array('return', 'int', 'int', array('int'), ''),
            array(
                'return',
                'int Number of Bobs',
                'int',
                array('int'),
                'Number of Bobs'
            ),
            array(
                'return',
                'int|double Number of Bobs',
                'int|double',
                array('int', 'double'),
                'Number of Bobs'
            ),
            array(
                'return',
                "int Number of \n Bobs",
                'int',
                array('int'),
                "Number of \n Bobs"
            ),
            array(
                'return',
                " int Number of Bobs",
                'int',
                array('int'),
                "Number of Bobs"
            ),
            array(
                'return',
                "int\nNumber of Bobs",
                'int',
                array('int'),
                "Number of Bobs"
            )
        );
    }
}
