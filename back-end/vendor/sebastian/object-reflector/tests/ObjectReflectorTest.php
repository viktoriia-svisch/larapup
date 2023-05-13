<?php
declare(strict_types=1);
namespace SebastianBergmann\ObjectReflector;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\ObjectReflector\TestFixture\ChildClass;
use SebastianBergmann\ObjectReflector\TestFixture\ClassWithIntegerAttributeName;
class ObjectReflectorTest extends TestCase
{
    private $objectReflector;
    protected function setUp()
    {
        $this->objectReflector = new ObjectReflector;
    }
    public function testReflectsAttributesOfObject()
    {
        $o = new ChildClass;
        $this->assertEquals(
            [
                'privateInChild' => 'private',
                'protectedInChild' => 'protected',
                'publicInChild' => 'public',
                'undeclared' => 'undeclared',
                'SebastianBergmann\ObjectReflector\TestFixture\ParentClass::privateInParent' => 'private',
                'SebastianBergmann\ObjectReflector\TestFixture\ParentClass::protectedInParent' => 'protected',
                'SebastianBergmann\ObjectReflector\TestFixture\ParentClass::publicInParent' => 'public',
            ],
            $this->objectReflector->getAttributes($o)
        );
    }
    public function testReflectsAttributeWithIntegerName()
    {
        $o = new ClassWithIntegerAttributeName;
        $this->assertEquals(
            [
                1 => 2
            ],
            $this->objectReflector->getAttributes($o)
        );
    }
    public function testRaisesExceptionWhenPassedArgumentIsNotAnObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->objectReflector->getAttributes(null);
    }
}
