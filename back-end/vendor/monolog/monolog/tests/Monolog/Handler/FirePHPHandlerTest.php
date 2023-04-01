<?php
namespace Monolog\Handler;
use Monolog\TestCase;
use Monolog\Logger;
class FirePHPHandlerTest extends TestCase
{
    public function setUp()
    {
        TestFirePHPHandler::resetStatic();
        $_SERVER['HTTP_USER_AGENT'] = 'Monolog Test; FirePHP/1.0';
    }
    public function testHeaders()
    {
        $handler = new TestFirePHPHandler;
        $handler->setFormatter($this->getIdentityFormatter());
        $handler->handle($this->getRecord(Logger::DEBUG));
        $handler->handle($this->getRecord(Logger::WARNING));
        $expected = array(
            'X-Wf-Protocol-1'    => 'http:
            'X-Wf-1-Structure-1' => 'http:
            'X-Wf-1-Plugin-1'    => 'http:
            'X-Wf-1-1-1-1'       => 'test',
            'X-Wf-1-1-1-2'       => 'test',
        );
        $this->assertEquals($expected, $handler->getHeaders());
    }
    public function testConcurrentHandlers()
    {
        $handler = new TestFirePHPHandler;
        $handler->setFormatter($this->getIdentityFormatter());
        $handler->handle($this->getRecord(Logger::DEBUG));
        $handler->handle($this->getRecord(Logger::WARNING));
        $handler2 = new TestFirePHPHandler;
        $handler2->setFormatter($this->getIdentityFormatter());
        $handler2->handle($this->getRecord(Logger::DEBUG));
        $handler2->handle($this->getRecord(Logger::WARNING));
        $expected = array(
            'X-Wf-Protocol-1'    => 'http:
            'X-Wf-1-Structure-1' => 'http:
            'X-Wf-1-Plugin-1'    => 'http:
            'X-Wf-1-1-1-1'       => 'test',
            'X-Wf-1-1-1-2'       => 'test',
        );
        $expected2 = array(
            'X-Wf-1-1-1-3'       => 'test',
            'X-Wf-1-1-1-4'       => 'test',
        );
        $this->assertEquals($expected, $handler->getHeaders());
        $this->assertEquals($expected2, $handler2->getHeaders());
    }
}
class TestFirePHPHandler extends FirePHPHandler
{
    protected $headers = array();
    public static function resetStatic()
    {
        self::$initialized = false;
        self::$sendHeaders = true;
        self::$messageIndex = 1;
    }
    protected function sendHeader($header, $content)
    {
        $this->headers[$header] = $content;
    }
    public function getHeaders()
    {
        return $this->headers;
    }
}
