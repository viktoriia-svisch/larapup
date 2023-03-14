<?php
namespace Monolog\Handler;
use Exception;
use Monolog\TestCase;
use Monolog\Logger;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
class RollbarHandlerTest extends TestCase
{
    private $rollbarNotifier;
    public $reportedExceptionArguments = null;
    protected function setUp()
    {
        parent::setUp();
        $this->setupRollbarNotifierMock();
    }
    public function testExceptionLogLevel()
    {
        $handler = $this->createHandler();
        $handler->handle($this->createExceptionRecord(Logger::DEBUG));
        $this->assertEquals('debug', $this->reportedExceptionArguments['payload']['level']);
    }
    private function setupRollbarNotifierMock()
    {
        $this->rollbarNotifier = $this->getMockBuilder('RollbarNotifier')
            ->setMethods(array('report_message', 'report_exception', 'flush'))
            ->getMock();
        $that = $this;
        $this->rollbarNotifier
            ->expects($this->any())
            ->method('report_exception')
            ->willReturnCallback(function ($exception, $context, $payload) use ($that) {
                $that->reportedExceptionArguments = compact('exception', 'context', 'payload');
            });
    }
    private function createHandler()
    {
        return new RollbarHandler($this->rollbarNotifier, Logger::DEBUG);
    }
    private function createExceptionRecord($level = Logger::DEBUG, $message = 'test', $exception = null)
    {
        return $this->getRecord($level, $message, array(
            'exception' => $exception ?: new Exception()
        ));
    }
}
