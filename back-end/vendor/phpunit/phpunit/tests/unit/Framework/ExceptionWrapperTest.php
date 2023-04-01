<?php
namespace PHPUnit\Framework;
class ExceptionWrapperTest extends TestCase
{
    public function testGetOriginalException(): void
    {
        $e       = new \BadFunctionCallException('custom class exception');
        $wrapper = new ExceptionWrapper($e);
        $this->assertInstanceOf(\BadFunctionCallException::class, $wrapper->getOriginalException());
    }
    public function testGetOriginalExceptionWithPrevious(): void
    {
        $e       = new \BadFunctionCallException('custom class exception', 0, new \Exception('previous'));
        $wrapper = new ExceptionWrapper($e);
        $this->assertInstanceOf(\BadFunctionCallException::class, $wrapper->getOriginalException());
    }
    public function testNoOriginalExceptionInStacktrace(): void
    {
        $e       = new \BadFunctionCallException('custom class exception');
        $wrapper = new ExceptionWrapper($e);
        $wrapper->setClassName('MyException');
        $data = \print_r($wrapper, 1);
        $this->assertNotContains(
            'BadFunctionCallException',
            $data,
            'Assert there is s no other BadFunctionCallException mention in stacktrace'
        );
    }
}
