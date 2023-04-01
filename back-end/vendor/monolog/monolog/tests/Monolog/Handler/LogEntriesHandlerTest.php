<?php
namespace Monolog\Handler;
use Monolog\TestCase;
use Monolog\Logger;
class LogEntriesHandlerTest extends TestCase
{
    private $res;
    private $handler;
    public function testWriteContent()
    {
        $this->createHandler();
        $this->handler->handle($this->getRecord(Logger::CRITICAL, 'Critical write test'));
        fseek($this->res, 0);
        $content = fread($this->res, 1024);
        $this->assertRegexp('/testToken \[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] test.CRITICAL: Critical write test/', $content);
    }
    public function testWriteBatchContent()
    {
        $records = array(
            $this->getRecord(),
            $this->getRecord(),
            $this->getRecord(),
        );
        $this->createHandler();
        $this->handler->handleBatch($records);
        fseek($this->res, 0);
        $content = fread($this->res, 1024);
        $this->assertRegexp('/(testToken \[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] .* \[\] \[\]\n){3}/', $content);
    }
    private function createHandler()
    {
        $useSSL = extension_loaded('openssl');
        $args = array('testToken', $useSSL, Logger::DEBUG, true);
        $this->res = fopen('php:
        $this->handler = $this->getMock(
            '\Monolog\Handler\LogEntriesHandler',
            array('fsockopen', 'streamSetTimeout', 'closeSocket'),
            $args
        );
        $reflectionProperty = new \ReflectionProperty('\Monolog\Handler\SocketHandler', 'connectionString');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->handler, 'localhost:1234');
        $this->handler->expects($this->any())
            ->method('fsockopen')
            ->will($this->returnValue($this->res));
        $this->handler->expects($this->any())
            ->method('streamSetTimeout')
            ->will($this->returnValue(true));
        $this->handler->expects($this->any())
            ->method('closeSocket')
            ->will($this->returnValue(true));
    }
}
