<?php
namespace Monolog;
use Monolog\Handler\TestHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\Test\LoggerInterfaceTest;
class PsrLogCompatTest extends LoggerInterfaceTest
{
    private $handler;
    public function getLogger()
    {
        $logger = new Logger('foo');
        $logger->pushHandler($handler = new TestHandler);
        $logger->pushProcessor(new PsrLogMessageProcessor);
        $handler->setFormatter(new LineFormatter('%level_name% %message%'));
        $this->handler = $handler;
        return $logger;
    }
    public function getLogs()
    {
        $convert = function ($record) {
            $lower = function ($match) {
                return strtolower($match[0]);
            };
            return preg_replace_callback('{^[A-Z]+}', $lower, $record['formatted']);
        };
        return array_map($convert, $this->handler->getRecords());
    }
}
