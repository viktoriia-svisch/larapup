<?php
namespace Monolog\Handler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\TestCase;
class FleepHookHandlerTest extends TestCase
{
    const TOKEN = '123abc';
    private $handler;
    public function setUp()
    {
        parent::setUp();
        if (!extension_loaded('openssl')) {
            $this->markTestSkipped('This test requires openssl extension to run');
        }
        $this->handler = new FleepHookHandler(self::TOKEN);
    }
    public function testConstructorSetsExpectedDefaults()
    {
        $this->assertEquals(Logger::DEBUG, $this->handler->getLevel());
        $this->assertEquals(true, $this->handler->getBubble());
    }
    public function testHandlerUsesLineFormatterWhichIgnoresEmptyArrays()
    {
        $record = array(
            'message' => 'msg',
            'context' => array(),
            'level' => Logger::DEBUG,
            'level_name' => Logger::getLevelName(Logger::DEBUG),
            'channel' => 'channel',
            'datetime' => new \DateTime(),
            'extra' => array(),
        );
        $expectedFormatter = new LineFormatter(null, null, true, true);
        $expected = $expectedFormatter->format($record);
        $handlerFormatter = $this->handler->getFormatter();
        $actual = $handlerFormatter->format($record);
        $this->assertEquals($expected, $actual, 'Empty context and extra arrays should not be rendered');
    }
    public function testConnectionStringisConstructedCorrectly()
    {
        $this->assertEquals('ssl:
    }
}
