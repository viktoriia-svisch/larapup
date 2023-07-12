<?php
use PHPUnit\Framework\TestCase;
class WithFormatterExpectationTest extends TestCase
{
    public function testFormatObjects($args, $expected)
    {
        $this->assertEquals(
            $expected,
            Mockery::formatObjects($args)
        );
    }
    public function testFormatObjectsWithMockCalledInGetterDoesNotLeadToRecursion()
    {
        $mock = Mockery::mock('stdClass');
        $mock->shouldReceive('doBar')->with('foo');
        $obj = new ClassWithGetter($mock);
        $this->expectException(\Mockery\Exception\NoMatchingExpectationException::class);
        $obj->getFoo();
    }
    public function formatObjectsDataProvider()
    {
        return array(
            array(
                array(null),
                ''
            ),
            array(
                array('a string', 98768, array('a', 'nother', 'array')),
                ''
            ),
        );
    }
    public function format_objects_should_not_call_getters_with_params()
    {
        $obj = new ClassWithGetterWithParam();
        $string = Mockery::formatObjects(array($obj));
        $this->assertTrue(\mb_strpos($string, 'Missing argument 1 for') === false);
    }
    public function testFormatObjectsExcludesStaticProperties()
    {
        $obj = new ClassWithPublicStaticProperty();
        $string = Mockery::formatObjects(array($obj));
        $this->assertTrue(\mb_strpos($string, 'excludedProperty') === false);
    }
    public function testFormatObjectsExcludesStaticGetters()
    {
        $obj = new ClassWithPublicStaticGetter();
        $string = Mockery::formatObjects(array($obj));
        $this->assertTrue(\mb_strpos($string, 'getExcluded') === false);
    }
}
class ClassWithGetter
{
    private $dep;
    public function __construct($dep)
    {
        $this->dep = $dep;
    }
    public function getFoo()
    {
        return $this->dep->doBar('bar', $this);
    }
}
class ClassWithGetterWithParam
{
    public function getBar($bar)
    {
    }
}
class ClassWithPublicStaticProperty
{
    public static $excludedProperty;
}
class ClassWithPublicStaticGetter
{
    public static function getExcluded()
    {
    }
}
