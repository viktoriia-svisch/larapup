<?php
namespace Monolog\Handler;
use Monolog\TestCase;
use Monolog\Logger;
class BufferHandlerTest extends TestCase
{
    private $shutdownCheckHandler;
    public function testHandleBuffers()
    {
        $test = new TestHandler();
        $handler = new BufferHandler($test);
        $handler->handle($this->getRecord(Logger::DEBUG));
        $handler->handle($this->getRecord(Logger::INFO));
        $this->assertFalse($test->hasDebugRecords());
        $this->assertFalse($test->hasInfoRecords());
        $handler->close();
        $this->assertTrue($test->hasInfoRecords());
        $this->assertTrue(count($test->getRecords()) === 2);
    }
    public function testPropagatesRecordsAtEndOfRequest()
    {
        $test = new TestHandler();
        $handler = new BufferHandler($test);
        $handler->handle($this->getRecord(Logger::WARNING));
        $handler->handle($this->getRecord(Logger::DEBUG));
        $this->shutdownCheckHandler = $test;
        register_shutdown_function(array($this, 'checkPropagation'));
    }
    public function checkPropagation()
    {
        if (!$this->shutdownCheckHandler->hasWarningRecords() || !$this->shutdownCheckHandler->hasDebugRecords()) {
            echo '!!! BufferHandlerTest::testPropagatesRecordsAtEndOfRequest failed to verify that the messages have been propagated' . PHP_EOL;
            exit(1);
        }
    }
    public function testHandleBufferLimit()
    {
        $test = new TestHandler();
        $handler = new BufferHandler($test, 2);
        $handler->handle($this->getRecord(Logger::DEBUG));
        $handler->handle($this->getRecord(Logger::DEBUG));
        $handler->handle($this->getRecord(Logger::INFO));
        $handler->handle($this->getRecord(Logger::WARNING));
        $handler->close();
        $this->assertTrue($test->hasWarningRecords());
        $this->assertTrue($test->hasInfoRecords());
        $this->assertFalse($test->hasDebugRecords());
    }
    public function testHandleBufferLimitWithFlushOnOverflow()
    {
        $test = new TestHandler();
        $handler = new BufferHandler($test, 3, Logger::DEBUG, true, true);
        $handler->handle($this->getRecord(Logger::DEBUG));
        $handler->handle($this->getRecord(Logger::DEBUG));
        $handler->handle($this->getRecord(Logger::DEBUG));
        $this->assertFalse($test->hasDebugRecords());
        $this->assertCount(0, $test->getRecords());
        $handler->handle($this->getRecord(Logger::INFO));
        $this->assertTrue($test->hasDebugRecords());
        $this->assertCount(3, $test->getRecords());
        $handler->handle($this->getRecord(Logger::WARNING));
        $this->assertCount(3, $test->getRecords());
        $handler->close();
        $this->assertCount(5, $test->getRecords());
        $this->assertTrue($test->hasWarningRecords());
        $this->assertTrue($test->hasInfoRecords());
    }
    public function testHandleLevel()
    {
        $test = new TestHandler();
        $handler = new BufferHandler($test, 0, Logger::INFO);
        $handler->handle($this->getRecord(Logger::DEBUG));
        $handler->handle($this->getRecord(Logger::INFO));
        $handler->handle($this->getRecord(Logger::WARNING));
        $handler->handle($this->getRecord(Logger::DEBUG));
        $handler->close();
        $this->assertTrue($test->hasWarningRecords());
        $this->assertTrue($test->hasInfoRecords());
        $this->assertFalse($test->hasDebugRecords());
    }
    public function testFlush()
    {
        $test = new TestHandler();
        $handler = new BufferHandler($test, 0);
        $handler->handle($this->getRecord(Logger::DEBUG));
        $handler->handle($this->getRecord(Logger::INFO));
        $handler->flush();
        $this->assertTrue($test->hasInfoRecords());
        $this->assertTrue($test->hasDebugRecords());
        $this->assertFalse($test->hasWarningRecords());
    }
    public function testHandleUsesProcessors()
    {
        $test = new TestHandler();
        $handler = new BufferHandler($test);
        $handler->pushProcessor(function ($record) {
            $record['extra']['foo'] = true;
            return $record;
        });
        $handler->handle($this->getRecord(Logger::WARNING));
        $handler->flush();
        $this->assertTrue($test->hasWarningRecords());
        $records = $test->getRecords();
        $this->assertTrue($records[0]['extra']['foo']);
    }
}
