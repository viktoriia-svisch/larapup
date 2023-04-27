<?php
namespace Barryvdh\Reflection\DocBlock\Tag;
class DeprecatedTagTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorParesInputsIntoCorrectFields(
        $type,
        $content,
        $exContent,
        $exDescription,
        $exVersion
    ) {
        $tag = new DeprecatedTag($type, $content);
        $this->assertEquals($type, $tag->getName());
        $this->assertEquals($exContent, $tag->getContent());
        $this->assertEquals($exDescription, $tag->getDescription());
        $this->assertEquals($exVersion, $tag->getVersion());
    }
    public function provideDataForConstuctor()
    {
        return array(
            array(
                'deprecated',
                '1.0 First release.',
                '1.0 First release.',
                'First release.',
                '1.0'
            ),
            array(
                'deprecated',
                "1.0\nFirst release.",
                "1.0\nFirst release.",
                'First release.',
                '1.0'
            ),
            array(
                'deprecated',
                "1.0\nFirst\nrelease.",
                "1.0\nFirst\nrelease.",
                "First\nrelease.",
                '1.0'
            ),
            array(
                'deprecated',
                'Unfinished release',
                'Unfinished release',
                'Unfinished release',
                ''
            ),
            array(
                'deprecated',
                '1.0',
                '1.0',
                '',
                '1.0'
            ),
            array(
                'deprecated',
                'GIT: $Id$',
                'GIT: $Id$',
                '',
                'GIT: $Id$'
            ),
            array(
                'deprecated',
                'GIT: $Id$ Dev build',
                'GIT: $Id$ Dev build',
                'Dev build',
                'GIT: $Id$'
            )
        );
    }
}
