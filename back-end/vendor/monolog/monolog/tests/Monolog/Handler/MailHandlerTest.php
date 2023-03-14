<?php
namespace Monolog\Handler;
use Monolog\Logger;
use Monolog\TestCase;
class MailHandlerTest extends TestCase
{
    public function testHandleBatch()
    {
        $formatter = $this->getMock('Monolog\\Formatter\\FormatterInterface');
        $formatter->expects($this->once())
            ->method('formatBatch'); 
        $handler = $this->getMockForAbstractClass('Monolog\\Handler\\MailHandler');
        $handler->expects($this->once())
            ->method('send');
        $handler->expects($this->never())
            ->method('write'); 
        $handler->setFormatter($formatter);
        $handler->handleBatch($this->getMultipleRecords());
    }
    public function testHandleBatchNotSendsMailIfMessagesAreBelowLevel()
    {
        $records = array(
            $this->getRecord(Logger::DEBUG, 'debug message 1'),
            $this->getRecord(Logger::DEBUG, 'debug message 2'),
            $this->getRecord(Logger::INFO, 'information'),
        );
        $handler = $this->getMockForAbstractClass('Monolog\\Handler\\MailHandler');
        $handler->expects($this->never())
            ->method('send');
        $handler->setLevel(Logger::ERROR);
        $handler->handleBatch($records);
    }
    public function testHandle()
    {
        $handler = $this->getMockForAbstractClass('Monolog\\Handler\\MailHandler');
        $record = $this->getRecord();
        $records = array($record);
        $records[0]['formatted'] = '['.$record['datetime']->format('Y-m-d H:i:s').'] test.WARNING: test [] []'."\n";
        $handler->expects($this->once())
            ->method('send')
            ->with($records[0]['formatted'], $records);
        $handler->handle($record);
    }
}
