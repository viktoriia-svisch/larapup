<?php
namespace Barryvdh\Reflection\DocBlock\Tag;
class UsesTagTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorParesInputsIntoCorrectFields(
        $type,
        $content,
        $exContent,
        $exDescription,
        $exReference
    ) {
        $tag = new UsesTag($type, $content);
        $this->assertEquals($type, $tag->getName());
        $this->assertEquals($exContent, $tag->getContent());
        $this->assertEquals($exDescription, $tag->getDescription());
        $this->assertEquals($exReference, $tag->getReference());
    }
    public function provideDataForConstuctor()
    {
        return array(
            array(
                'uses',
                'Foo::bar()',
                'Foo::bar()',
                '',
                'Foo::bar()'
            ),
            array(
                'uses',
                'Foo::bar() Testing',
                'Foo::bar() Testing',
                'Testing',
                'Foo::bar()',
            ),
            array(
                'uses',
                'Foo::bar() Testing comments',
                'Foo::bar() Testing comments',
                'Testing comments',
                'Foo::bar()',
            ),
        );
    }
}
