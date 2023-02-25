<?php
namespace Psy\Test\Exception;
use Psy\Exception\RuntimeException;
class RuntimeExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testException()
    {
        $msg = 'bananas';
        $e   = new RuntimeException($msg);
        $this->assertInstanceOf('Psy\Exception\Exception', $e);
        $this->assertInstanceOf('RuntimeException', $e);
        $this->assertInstanceOf('Psy\Exception\RuntimeException', $e);
        $this->assertSame($msg, $e->getMessage());
        $this->assertSame($msg, $e->getRawMessage());
    }
}
