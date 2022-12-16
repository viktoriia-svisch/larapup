<?php
namespace Monolog\Handler;
use Monolog\TestCase;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\WebProcessor;
class AbstractHandlerTest extends TestCase
{
    public function testConstructAndGetSet()
    {
        $handler = $this->getMockForAbstractClass('Monolog\Handler\AbstractHandler', array(Logger::WARNING, false));
        $this->assertEquals(Logger::WARNING, $handler->getLevel());
        $this->assertEquals(false, $handler->getBubble());
        $handler->setLevel(Logger::ERROR);
        $handler->setBubble(true);
        $handler->setFormatter($formatter = new LineFormatter);
        $this->assertEquals(Logger::ERROR, $handler->getLevel());
        $this->assertEquals(true, $handler->getBubble());
        $this->assertSame($formatter, $handler->getFormatter());
    }
    public function testHandleBatch()
    {
        $handler = $this->getMockForAbstractClass('Monolog\Handler\AbstractHandler');
        $handler->expects($this->exactly(2))
            ->method('handle');
        $handler->handleBatch(array($this->getRecord(), $this->getRecord()));
    }
    public function testIsHandling()
    {
        $handler = $this->getMockForAbstractClass('Monolog\Handler\AbstractHandler', array(Logger::WARNING, false));
        $this->assertTrue($handler->isHandling($this->getRecord()));
        $this->assertFalse($handler->isHandling($this->getRecord(Logger::DEBUG)));
    }
    public function testHandlesPsrStyleLevels()
    {
        $handler = $this->getMockForAbstractClass('Monolog\Handler\AbstractHandler', array('warning', false));
        $this->assertFalse($handler->isHandling($this->getRecord(Logger::DEBUG)));
        $handler->setLevel('debug');
        $this->assertTrue($handler->isHandling($this->getRecord(Logger::DEBUG)));
    }
    public function testGetFormatterInitializesDefault()
    {
        $handler = $this->getMockForAbstractClass('Monolog\Handler\AbstractHandler');
        $this->assertInstanceOf('Monolog\Formatter\LineFormatter', $handler->getFormatter());
    }
    public function testPushPopProcessor()
    {
        $logger = $this->getMockForAbstractClass('Monolog\Handler\AbstractHandler');
        $processor1 = new WebProcessor;
        $processor2 = new WebProcessor;
        $logger->pushProcessor($processor1);
        $logger->pushProcessor($processor2);
        $this->assertEquals($processor2, $logger->popProcessor());
        $this->assertEquals($processor1, $logger->popProcessor());
        $logger->popProcessor();
    }
    public function testPushProcessorWithNonCallable()
    {
        $handler = $this->getMockForAbstractClass('Monolog\Handler\AbstractHandler');
        $handler->pushProcessor(new \stdClass());
    }
}
