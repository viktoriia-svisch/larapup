<?php
namespace Mockery\Generator;
use PHPUnit\Framework\TestCase;
class MockConfigurationTest extends TestCase
{
    public function blackListedMethodsShouldNotBeInListToBeMocked()
    {
        $config = new MockConfiguration(array("Mockery\Generator\\TestSubject"), array("foo"));
        $methods = $config->getMethodsToMock();
        $this->assertCount(1, $methods);
        $this->assertEquals("bar", $methods[0]->getName());
    }
    public function blackListsAreCaseInsensitive()
    {
        $config = new MockConfiguration(array("Mockery\Generator\\TestSubject"), array("FOO"));
        $methods = $config->getMethodsToMock();
        $this->assertCount(1, $methods);
        $this->assertEquals("bar", $methods[0]->getName());
    }
    public function onlyWhiteListedMethodsShouldBeInListToBeMocked()
    {
        $config = new MockConfiguration(array("Mockery\Generator\\TestSubject"), array(), array('foo'));
        $methods = $config->getMethodsToMock();
        $this->assertCount(1, $methods);
        $this->assertEquals("foo", $methods[0]->getName());
    }
    public function whitelistOverRulesBlackList()
    {
        $config = new MockConfiguration(array("Mockery\Generator\\TestSubject"), array("foo"), array("foo"));
        $methods = $config->getMethodsToMock();
        $this->assertCount(1, $methods);
        $this->assertEquals("foo", $methods[0]->getName());
    }
    public function whiteListsAreCaseInsensitive()
    {
        $config = new MockConfiguration(array("Mockery\Generator\\TestSubject"), array(), array("FOO"));
        $methods = $config->getMethodsToMock();
        $this->assertCount(1, $methods);
        $this->assertEquals("foo", $methods[0]->getName());
    }
    public function finalMethodsAreExcluded()
    {
        $config = new MockConfiguration(array("Mockery\Generator\\ClassWithFinalMethod"));
        $methods = $config->getMethodsToMock();
        $this->assertCount(1, $methods);
        $this->assertEquals("bar", $methods[0]->getName());
    }
    public function shouldIncludeMethodsFromAllTargets()
    {
        $config = new MockConfiguration(array("Mockery\\Generator\\TestInterface", "Mockery\\Generator\\TestInterface2"));
        $methods = $config->getMethodsToMock();
        $this->assertCount(2, $methods);
    }
    public function shouldThrowIfTargetClassIsFinal()
    {
        $this->expectException(\Mockery\Exception::class);
        $config = new MockConfiguration(array("Mockery\\Generator\\TestFinal"));
        $config->getTargetClass();
    }
    public function shouldTargetIteratorAggregateIfTryingToMockTraversable()
    {
        $config = new MockConfiguration(array("\\Traversable"));
        $interfaces = $config->getTargetInterfaces();
        $this->assertCount(1, $interfaces);
        $first = array_shift($interfaces);
        $this->assertEquals("IteratorAggregate", $first->getName());
    }
    public function shouldTargetIteratorAggregateIfTraversableInTargetsTree()
    {
        $config = new MockConfiguration(array("Mockery\Generator\TestTraversableInterface"));
        $interfaces = $config->getTargetInterfaces();
        $this->assertCount(2, $interfaces);
        $this->assertEquals("IteratorAggregate", $interfaces[0]->getName());
        $this->assertEquals("Mockery\Generator\TestTraversableInterface", $interfaces[1]->getName());
    }
    public function shouldBringIteratorToHeadOfTargetListIfTraversablePresent()
    {
        $config = new MockConfiguration(array("Mockery\Generator\TestTraversableInterface2"));
        $interfaces = $config->getTargetInterfaces();
        $this->assertCount(2, $interfaces);
        $this->assertEquals("Iterator", $interfaces[0]->getName());
        $this->assertEquals("Mockery\Generator\TestTraversableInterface2", $interfaces[1]->getName());
    }
    public function shouldBringIteratorAggregateToHeadOfTargetListIfTraversablePresent()
    {
        $config = new MockConfiguration(array("Mockery\Generator\TestTraversableInterface3"));
        $interfaces = $config->getTargetInterfaces();
        $this->assertCount(2, $interfaces);
        $this->assertEquals("IteratorAggregate", $interfaces[0]->getName());
        $this->assertEquals("Mockery\Generator\TestTraversableInterface3", $interfaces[1]->getName());
    }
}
interface TestTraversableInterface extends \Traversable
{
}
interface TestTraversableInterface2 extends \Traversable, \Iterator
{
}
interface TestTraversableInterface3 extends \Traversable, \IteratorAggregate
{
}
final class TestFinal
{
}
interface TestInterface
{
    public function foo();
}
interface TestInterface2
{
    public function bar();
}
class TestSubject
{
    public function foo()
    {
    }
    public function bar()
    {
    }
}
class ClassWithFinalMethod
{
    final public function foo()
    {
    }
    public function bar()
    {
    }
}
