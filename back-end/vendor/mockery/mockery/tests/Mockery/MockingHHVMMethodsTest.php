<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Generator\Method;
use test\Mockery\Fixtures\MethodWithHHVMReturnType;
class MockingHHVMMethodsTest extends MockeryTestCase
{
    private $container;
    protected function mockeryTestSetUp()
    {
        if (!$this->isHHVM()) {
            $this->markTestSkipped('For HHVM test only');
        }
        parent::mockeryTestSetUp();
        require_once __DIR__."/Fixtures/MethodWithHHVMReturnType.php";
    }
    public function it_strip_hhvm_array_return_types()
    {
        $mock = mock('test\Mockery\Fixtures\MethodWithHHVMReturnType');
        $mock->shouldReceive('nullableHHVMArray')->andReturn(array('key' => true));
        $mock->nullableHHVMArray();
    }
    public function it_strip_hhvm_void_return_types()
    {
        $mock = mock('test\Mockery\Fixtures\MethodWithHHVMReturnType');
        $mock->shouldReceive('HHVMVoid')->andReturnNull();
        $mock->HHVMVoid();
    }
    public function it_strip_hhvm_mixed_return_types()
    {
        $mock = mock('test\Mockery\Fixtures\MethodWithHHVMReturnType');
        $mock->shouldReceive('HHVMMixed')->andReturnNull();
        $mock->HHVMMixed();
    }
    public function it_strip_hhvm_this_return_types()
    {
        $mock = mock('test\Mockery\Fixtures\MethodWithHHVMReturnType');
        $mock->shouldReceive('HHVMThis')->andReturn(new MethodWithHHVMReturnType());
        $mock->HHVMThis();
    }
    public function it_allow_hhvm_string_return_types()
    {
        $mock = mock('test\Mockery\Fixtures\MethodWithHHVMReturnType');
        $mock->shouldReceive('HHVMString')->andReturn('a string');
        $mock->HHVMString();
    }
    public function it_allow_hhvm_imm_vector_return_types()
    {
        $mock = mock('test\Mockery\Fixtures\MethodWithHHVMReturnType');
        $mock->shouldReceive('HHVMImmVector')->andReturn(new \HH\ImmVector([1, 2, 3]));
        $mock->HHVMImmVector();
    }
    private function isHHVM()
    {
        return \defined('HHVM_VERSION');
    }
}
