<?php
namespace Psy\Test\Exception;
use Psy\Exception\BreakException;
class BreakExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testInstance()
    {
        $e = new BreakException();
        $this->assertInstanceOf('Psy\Exception\Exception', $e);
        $this->assertInstanceOf('Psy\Exception\BreakException', $e);
    }
    public function testMessage()
    {
        $e = new BreakException('foo');
        $this->assertContains('foo', $e->getMessage());
        $this->assertSame('foo', $e->getRawMessage());
    }
    public function testExitShell()
    {
        BreakException::exitShell();
    }
}
