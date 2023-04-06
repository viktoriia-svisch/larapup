<?php
namespace Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
class TagTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidTagLine()
    {
        Tag::createInstance('Invalid tag line');
    }
    public function testTagHandlerUnregistration()
    {
        $currentHandler = __NAMESPACE__ . '\Tag\VarTag';
        $tagPreUnreg = Tag::createInstance('@var mixed');
        $this->assertInstanceOf(
            $currentHandler,
            $tagPreUnreg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPreUnreg
        );
        Tag::registerTagHandler('var', null);
        $tagPostUnreg = Tag::createInstance('@var mixed');
        $this->assertNotInstanceOf(
            $currentHandler,
            $tagPostUnreg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPostUnreg
        );
        Tag::registerTagHandler('var', $currentHandler);
    }
    public function testTagHandlerCorrectRegistration()
    {
        if (0 == ini_get('allow_url_include')) {
            $this->markTestSkipped('"data" URIs for includes are required.');
        }
        $currentHandler = __NAMESPACE__ . '\Tag\VarTag';
        $tagPreReg = Tag::createInstance('@var mixed');
        $this->assertInstanceOf(
            $currentHandler,
            $tagPreReg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPreReg
        );
        include 'data:text/plain;base64,'. base64_encode(
<<<TAG_HANDLER
<?php
    class MyTagHandler extends \Barryvdh\Reflection\DocBlock\Tag {}
TAG_HANDLER
        );
        $this->assertTrue(Tag::registerTagHandler('var', '\MyTagHandler'));
        $tagPostReg = Tag::createInstance('@var mixed');
        $this->assertNotInstanceOf(
            $currentHandler,
            $tagPostReg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPostReg
        );
        $this->assertInstanceOf(
            '\MyTagHandler',
            $tagPostReg
        );
        $this->assertTrue(Tag::registerTagHandler('var', $currentHandler));
    }
    public function testNamespacedTagHandlerCorrectRegistration()
    {
        $tagPreReg = Tag::createInstance('@T something');
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPreReg
        );
        $this->assertNotInstanceOf(
            '\MyTagHandler',
            $tagPreReg
        );
        $this->assertTrue(
            Tag::registerTagHandler('\MyNamespace\MyTag', '\MyTagHandler')
        );
        $tagPostReg = Tag::createInstance(
            '@T something',
            new DocBlock(
                '',
                new Context('', array('T' => '\MyNamespace\MyTag'))
            )
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPostReg
        );
        $this->assertInstanceOf(
            '\MyTagHandler',
            $tagPostReg
        );
        $this->assertTrue(
            Tag::registerTagHandler('\MyNamespace\MyTag', null)
        );
    }
    public function testNamespacedTagHandlerIncorrectRegistration()
    {
        $tagPreReg = Tag::createInstance('@T something');
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPreReg
        );
        $this->assertNotInstanceOf(
            '\MyTagHandler',
            $tagPreReg
        );
        $this->assertFalse(
            Tag::registerTagHandler('MyNamespace\MyTag', '\MyTagHandler')
        );
        $tagPostReg = Tag::createInstance(
            '@T something',
            new DocBlock(
                '',
                new Context('', array('T' => '\MyNamespace\MyTag'))
            )
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPostReg
        );
        $this->assertNotInstanceOf(
            '\MyTagHandler',
            $tagPostReg
        );
    }
    public function testNonExistentTagHandlerRegistration()
    {
        $currentHandler = __NAMESPACE__ . '\Tag\VarTag';
        $tagPreReg = Tag::createInstance('@var mixed');
        $this->assertInstanceOf(
            $currentHandler,
            $tagPreReg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPreReg
        );
        $this->assertFalse(Tag::registerTagHandler('var', 'Non existent'));
        $tagPostReg = Tag::createInstance('@var mixed');
        $this->assertInstanceOf(
            $currentHandler,
            $tagPostReg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPostReg
        );
    }
    public function testIncompatibleTagHandlerRegistration()
    {
        $currentHandler = __NAMESPACE__ . '\Tag\VarTag';
        $tagPreReg = Tag::createInstance('@var mixed');
        $this->assertInstanceOf(
            $currentHandler,
            $tagPreReg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPreReg
        );
        $this->assertFalse(
            Tag::registerTagHandler('var', __NAMESPACE__ . '\TagTest')
        );
        $tagPostReg = Tag::createInstance('@var mixed');
        $this->assertInstanceOf(
            $currentHandler,
            $tagPostReg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPostReg
        );
    }
    public function testConstructorParesInputsIntoCorrectFields(
        $type,
        $content,
        $exDescription
    ) {
        $tag = new Tag($type, $content);
        $this->assertEquals($type, $tag->getName());
        $this->assertEquals($content, $tag->getContent());
        $this->assertEquals($exDescription, $tag->getDescription());
    }
    public function provideDataForConstuctor()
    {
        return array(
            array(
                'unknown',
                'some content',
                'some content',
            ),
            array(
                'unknown',
                '',
                '',
            )
        );
    }
}
