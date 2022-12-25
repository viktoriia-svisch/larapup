<?php
use Mockery\Adapter\Phpunit\MockeryTestCase;
class Mockery_AdhocTest extends MockeryTestCase
{
    public function mockeryTestSetUp()
    {
        $this->container = new \Mockery\Container(\Mockery::getDefaultGenerator(), \Mockery::getDefaultLoader());
    }
    public function mockeryTestTearDown()
    {
        $this->container->mockery_close();
    }
    public function testSimplestMockCreation()
    {
        $m = $this->container->mock('MockeryTest_NameOfExistingClass');
        $this->assertInstanceOf(MockeryTest_NameOfExistingClass::class, $m);
    }
    public function testMockeryInterfaceForClass()
    {
        $m = $this->container->mock('SplFileInfo');
        $this->assertInstanceOf(\Mockery\MockInterface::class, $m);
    }
    public function testMockeryInterfaceForNonExistingClass()
    {
        $m = $this->container->mock('ABC_IDontExist');
        $this->assertInstanceOf(\Mockery\MockInterface::class, $m);
    }
    public function testMockeryInterfaceForInterface()
    {
        $m = $this->container->mock('MockeryTest_NameOfInterface');
        $this->assertInstanceOf(\Mockery\MockInterface::class, $m);
    }
    public function testMockeryInterfaceForAbstract()
    {
        $m = $this->container->mock('MockeryTest_NameOfAbstract');
        $this->assertInstanceOf(\Mockery\MockInterface::class, $m);
    }
    public function testInvalidCountExceptionThrowsRuntimeExceptionOnIllegalComparativeSymbol()
    {
        $this->expectException('Mockery\Exception\RuntimeException');
        $e = new \Mockery\Exception\InvalidCountException;
        $e->setExpectedCountComparative('X');
    }
    public function testMockeryConstructAndDestructIsNotCalled()
    {
        MockeryTest_NameOfExistingClassWithDestructor::$isDestructorWasCalled = false;
        $this->container->mock('MockeryTest_NameOfExistingClassWithDestructor');
        $this->container->mockery_close();
        $this->assertFalse(MockeryTest_NameOfExistingClassWithDestructor::$isDestructorWasCalled);
    }
    public function testMockeryConstructAndDestructIsCalled()
    {
        MockeryTest_NameOfExistingClassWithDestructor::$isDestructorWasCalled = false;
        $this->container->mock('MockeryTest_NameOfExistingClassWithDestructor', array());
        $this->container->mockery_close();
        $this->assertTrue(MockeryTest_NameOfExistingClassWithDestructor::$isDestructorWasCalled);
    }
}
class MockeryTest_NameOfExistingClass
{
}
interface MockeryTest_NameOfInterface
{
    public function foo();
}
abstract class MockeryTest_NameOfAbstract
{
    abstract public function foo();
}
class MockeryTest_NameOfExistingClassWithDestructor
{
    public static $isDestructorWasCalled = false;
    public function __destruct()
    {
        self::$isDestructorWasCalled = true;
    }
}
