<?php
namespace Monolog\Handler;
use Monolog\TestCase;
use Monolog\Logger;
class SocketHandlerTest extends TestCase
{
    private $handler;
    private $res;
    public function testInvalidHostname()
    {
        $this->createHandler('garbage:
        $this->writeRecord('data');
    }
    public function testBadConnectionTimeout()
    {
        $this->createHandler('localhost:1234');
        $this->handler->setConnectionTimeout(-1);
    }
    public function testSetConnectionTimeout()
    {
        $this->createHandler('localhost:1234');
        $this->handler->setConnectionTimeout(10.1);
        $this->assertEquals(10.1, $this->handler->getConnectionTimeout());
    }
    public function testBadTimeout()
    {
        $this->createHandler('localhost:1234');
        $this->handler->setTimeout(-1);
    }
    public function testSetTimeout()
    {
        $this->createHandler('localhost:1234');
        $this->handler->setTimeout(10.25);
        $this->assertEquals(10.25, $this->handler->getTimeout());
    }
    public function testSetWritingTimeout()
    {
        $this->createHandler('localhost:1234');
        $this->handler->setWritingTimeout(10.25);
        $this->assertEquals(10.25, $this->handler->getWritingTimeout());
    }
    public function testSetChunkSize()
    {
        $this->createHandler('localhost:1234');
        $this->handler->setChunkSize(1025);
        $this->assertEquals(1025, $this->handler->getChunkSize());
    }
    public function testSetConnectionString()
    {
        $this->createHandler('tcp:
        $this->assertEquals('tcp:
    }
    public function testExceptionIsThrownOnFsockopenError()
    {
        $this->setMockHandler(array('fsockopen'));
        $this->handler->expects($this->once())
            ->method('fsockopen')
            ->will($this->returnValue(false));
        $this->writeRecord('Hello world');
    }
    public function testExceptionIsThrownOnPfsockopenError()
    {
        $this->setMockHandler(array('pfsockopen'));
        $this->handler->expects($this->once())
            ->method('pfsockopen')
            ->will($this->returnValue(false));
        $this->handler->setPersistent(true);
        $this->writeRecord('Hello world');
    }
    public function testExceptionIsThrownIfCannotSetTimeout()
    {
        $this->setMockHandler(array('streamSetTimeout'));
        $this->handler->expects($this->once())
            ->method('streamSetTimeout')
            ->will($this->returnValue(false));
        $this->writeRecord('Hello world');
    }
    public function testExceptionIsThrownIfCannotSetChunkSize()
    {
        $this->setMockHandler(array('streamSetChunkSize'));
        $this->handler->setChunkSize(8192);
        $this->handler->expects($this->once())
            ->method('streamSetChunkSize')
            ->will($this->returnValue(false));
        $this->writeRecord('Hello world');
    }
    public function testWriteFailsOnIfFwriteReturnsFalse()
    {
        $this->setMockHandler(array('fwrite'));
        $callback = function ($arg) {
            $map = array(
                'Hello world' => 6,
                'world' => false,
            );
            return $map[$arg];
        };
        $this->handler->expects($this->exactly(2))
            ->method('fwrite')
            ->will($this->returnCallback($callback));
        $this->writeRecord('Hello world');
    }
    public function testWriteFailsIfStreamTimesOut()
    {
        $this->setMockHandler(array('fwrite', 'streamGetMetadata'));
        $callback = function ($arg) {
            $map = array(
                'Hello world' => 6,
                'world' => 5,
            );
            return $map[$arg];
        };
        $this->handler->expects($this->exactly(1))
            ->method('fwrite')
            ->will($this->returnCallback($callback));
        $this->handler->expects($this->exactly(1))
            ->method('streamGetMetadata')
            ->will($this->returnValue(array('timed_out' => true)));
        $this->writeRecord('Hello world');
    }
    public function testWriteFailsOnIncompleteWrite()
    {
        $this->setMockHandler(array('fwrite', 'streamGetMetadata'));
        $res = $this->res;
        $callback = function ($string) use ($res) {
            fclose($res);
            return strlen('Hello');
        };
        $this->handler->expects($this->exactly(1))
            ->method('fwrite')
            ->will($this->returnCallback($callback));
        $this->handler->expects($this->exactly(1))
            ->method('streamGetMetadata')
            ->will($this->returnValue(array('timed_out' => false)));
        $this->writeRecord('Hello world');
    }
    public function testWriteWithMemoryFile()
    {
        $this->setMockHandler();
        $this->writeRecord('test1');
        $this->writeRecord('test2');
        $this->writeRecord('test3');
        fseek($this->res, 0);
        $this->assertEquals('test1test2test3', fread($this->res, 1024));
    }
    public function testWriteWithMock()
    {
        $this->setMockHandler(array('fwrite'));
        $callback = function ($arg) {
            $map = array(
                'Hello world' => 6,
                'world' => 5,
            );
            return $map[$arg];
        };
        $this->handler->expects($this->exactly(2))
            ->method('fwrite')
            ->will($this->returnCallback($callback));
        $this->writeRecord('Hello world');
    }
    public function testClose()
    {
        $this->setMockHandler();
        $this->writeRecord('Hello world');
        $this->assertInternalType('resource', $this->res);
        $this->handler->close();
        $this->assertFalse(is_resource($this->res), "Expected resource to be closed after closing handler");
    }
    public function testCloseDoesNotClosePersistentSocket()
    {
        $this->setMockHandler();
        $this->handler->setPersistent(true);
        $this->writeRecord('Hello world');
        $this->assertTrue(is_resource($this->res));
        $this->handler->close();
        $this->assertTrue(is_resource($this->res));
    }
    public function testAvoidInfiniteLoopWhenNoDataIsWrittenForAWritingTimeoutSeconds()
    {
        $this->setMockHandler(array('fwrite', 'streamGetMetadata'));
        $this->handler->expects($this->any())
            ->method('fwrite')
            ->will($this->returnValue(0));
        $this->handler->expects($this->any())
            ->method('streamGetMetadata')
            ->will($this->returnValue(array('timed_out' => false)));
        $this->handler->setWritingTimeout(1);
        $this->writeRecord('Hello world');
    }
    private function createHandler($connectionString)
    {
        $this->handler = new SocketHandler($connectionString);
        $this->handler->setFormatter($this->getIdentityFormatter());
    }
    private function writeRecord($string)
    {
        $this->handler->handle($this->getRecord(Logger::WARNING, $string));
    }
    private function setMockHandler(array $methods = array())
    {
        $this->res = fopen('php:
        $defaultMethods = array('fsockopen', 'pfsockopen', 'streamSetTimeout');
        $newMethods = array_diff($methods, $defaultMethods);
        $finalMethods = array_merge($defaultMethods, $newMethods);
        $this->handler = $this->getMock(
            '\Monolog\Handler\SocketHandler', $finalMethods, array('localhost:1234')
        );
        if (!in_array('fsockopen', $methods)) {
            $this->handler->expects($this->any())
                ->method('fsockopen')
                ->will($this->returnValue($this->res));
        }
        if (!in_array('pfsockopen', $methods)) {
            $this->handler->expects($this->any())
                ->method('pfsockopen')
                ->will($this->returnValue($this->res));
        }
        if (!in_array('streamSetTimeout', $methods)) {
            $this->handler->expects($this->any())
                ->method('streamSetTimeout')
                ->will($this->returnValue(true));
        }
        if (!in_array('streamSetChunkSize', $methods)) {
            $this->handler->expects($this->any())
                ->method('streamSetChunkSize')
                ->will($this->returnValue(8192));
        }
        $this->handler->setFormatter($this->getIdentityFormatter());
    }
}
