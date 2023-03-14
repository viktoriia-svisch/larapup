<?php
use Mockery\Adapter\Phpunit\MockeryTestCase;
class GlobalHelpersTest extends MockeryTestCase
{
    public function mockeryTestSetUp()
    {
        \Mockery::globalHelpers();
    }
    public function mockeryTestTearDown()
    {
        \Mockery::close();
    }
    public function mock_creates_a_mock()
    {
        $double = mock();
        $this->assertInstanceOf(\Mockery\MockInterface::class, $double);
        $this->expectException(\Exception::class);
        $double->foo();
    }
    public function spy_creates_a_spy()
    {
        $double = spy();
        $this->assertInstanceOf(\Mockery\MockInterface::class, $double);
        $double->foo();
    }
    public function named_mock_creates_a_named_mock()
    {
        $className = "Class".uniqid();
        $double = namedMock($className);
        $this->assertInstanceOf(\Mockery\MockInterface::class, $double);
        $this->assertInstanceOf($className, $double);
    }
}
