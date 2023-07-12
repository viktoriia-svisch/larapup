<?php
namespace tests\Mockery\Generator;
use Mockery as m;
use Mockery\Generator\MockConfigurationBuilder;
use PHPUnit\Framework\TestCase;
class MockConfigurationBuilderTest extends TestCase
{
    public function reservedWordsAreBlackListedByDefault()
    {
        $builder = new MockConfigurationBuilder;
        $this->assertContains('__halt_compiler', $builder->getMockConfiguration()->getBlackListedMethods());
        $this->markTestSkipped("Need a builtin class with a method that is a reserved word");
    }
    public function magicMethodsAreBlackListedByDefault()
    {
        $builder = new MockConfigurationBuilder;
        $builder->addTarget(ClassWithMagicCall::class);
        $methods = $builder->getMockConfiguration()->getMethodsToMock();
        $this->assertCount(1, $methods);
        $this->assertEquals("foo", $methods[0]->getName());
    }
    public function xdebugs_debug_info_is_black_listed_by_default()
    {
        $builder = new MockConfigurationBuilder;
        $builder->addTarget(ClassWithDebugInfo::class);
        $methods = $builder->getMockConfiguration()->getMethodsToMock();
        $this->assertCount(1, $methods);
        $this->assertEquals("foo", $methods[0]->getName());
    }
}
class ClassWithMagicCall
{
    public function foo()
    {
    }
    public function __call($method, $args)
    {
    }
}
class ClassWithDebugInfo
{
    public function foo()
    {
    }
    public function __debugInfo()
    {
    }
}
