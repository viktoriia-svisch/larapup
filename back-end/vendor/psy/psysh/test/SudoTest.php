<?php
namespace Psy\Test;
use Psy\Sudo;
class SudoTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        if (\version_compare(PHP_VERSION, '7.1.0', '<')) {
            $this->markTestSkipped('YOLO');
        }
    }
    public function testFetchProperty()
    {
        $obj = new ClassWithSecrets();
        $this->assertSame('private and prop', Sudo::fetchProperty($obj, 'privateProp'));
    }
    public function testAssignProperty()
    {
        $obj = new ClassWithSecrets();
        $this->assertSame('private and prop', Sudo::fetchProperty($obj, 'privateProp'));
        $this->assertSame('not so private now', Sudo::assignProperty($obj, 'privateProp', 'not so private now'));
        $this->assertSame('not so private now', Sudo::fetchProperty($obj, 'privateProp'));
    }
    public function testCallMethod()
    {
        $obj = new ClassWithSecrets();
        $this->assertSame('private and method', Sudo::callMethod($obj, 'privateMethod'));
        $this->assertSame('private and method with 1', Sudo::callMethod($obj, 'privateMethod', 1));
        $this->assertSame(
            'private and method with ["foo",2]',
            Sudo::callMethod($obj, 'privateMethod', ['foo', 2]
        ));
    }
    public function testFetchStaticProperty()
    {
        $obj = new ClassWithSecrets();
        $this->assertSame('private and static and prop', Sudo::fetchStaticProperty($obj, 'privateStaticProp'));
    }
    public function testAssignStaticProperty()
    {
        $obj = new ClassWithSecrets();
        $this->assertSame('private and static and prop', Sudo::fetchStaticProperty($obj, 'privateStaticProp'));
        $this->assertSame('not so private now', Sudo::assignStaticProperty($obj, 'privateStaticProp', 'not so private now'));
        $this->assertSame('not so private now', Sudo::fetchStaticProperty($obj, 'privateStaticProp'));
    }
    public function testCallStatic()
    {
        $obj = new ClassWithSecrets();
        $this->assertSame('private and static and method', Sudo::callStatic($obj, 'privateStaticMethod'));
        $this->assertSame('private and static and method with 1', Sudo::callStatic($obj, 'privateStaticMethod', 1));
        $this->assertSame(
            'private and static and method with ["foo",2]',
            Sudo::callStatic($obj, 'privateStaticMethod', ['foo', 2]
        ));
    }
    public function testFetchClassConst()
    {
        $obj = new ClassWithSecrets();
        $this->assertSame('private and const', Sudo::fetchClassConst($obj, 'PRIVATE_CONST'));
    }
}
