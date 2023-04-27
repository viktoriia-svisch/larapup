<?php
namespace Barryvdh\Reflection\DocBlock\Tag;
class LinkTagTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorParesInputsIntoCorrectFields(
        $type,
        $content,
        $exContent,
        $exDescription,
        $exLink
    ) {
        $tag = new LinkTag($type, $content);
        $this->assertEquals($type, $tag->getName());
        $this->assertEquals($exContent, $tag->getContent());
        $this->assertEquals($exDescription, $tag->getDescription());
        $this->assertEquals($exLink, $tag->getLink());
    }
    public function provideDataForConstuctor()
    {
        return array(
            array(
                'link',
                'http:
                'http:
                'http:
                'http:
            ),
            array(
                'link',
                'http:
                'http:
                'Testing',
                'http:
            ),
            array(
                'link',
                'http:
                'http:
                'Testing comments',
                'http:
            ),
        );
    }
}
