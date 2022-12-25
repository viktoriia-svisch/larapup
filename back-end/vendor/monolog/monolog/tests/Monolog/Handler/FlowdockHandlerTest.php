<?php
namespace Monolog\Handler;
use Monolog\Formatter\FlowdockFormatter;
use Monolog\TestCase;
use Monolog\Logger;
class FlowdockHandlerTest extends TestCase
{
    private $res;
    private $handler;
    public function setUp()
    {
        if (!extension_loaded('openssl')) {
            $this->markTestSkipped('This test requires openssl to run');
        }
    }
    public function testWriteHeader()
    {
        $this->createHandler();
        $this->handler->handle($this->getRecord(Logger::CRITICAL, 'test1'));
        fseek($this->res, 0);
        $content = fread($this->res, 1024);
        $this->assertRegexp('/POST \/v1\/messages\/team_inbox\/.* HTTP\/1.1\\r\\nHost: api.flowdock.com\\r\\nContent-Type: application\/json\\r\\nContent-Length: \d{2,4}\\r\\n\\r\\n/', $content);
        return $content;
    }
    public function testWriteContent($content)
    {
        $this->assertRegexp('/"source":"test_source"/', $content);
        $this->assertRegexp('/"from_address":"source@test\.com"/', $content);
    }
    private function createHandler($token = 'myToken')
    {
        $constructorArgs = array($token, Logger::DEBUG);
        $this->res = fopen('php:
        $this->handler = $this->getMock(
            '\Monolog\Handler\FlowdockHandler',
            array('fsockopen', 'streamSetTimeout', 'closeSocket'),
            $constructorArgs
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
        $this->handler->setFormatter(new FlowdockFormatter('test_source', 'source@test.com'));
    }
}
