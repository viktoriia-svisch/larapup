<?php
namespace Monolog\Handler;
use Monolog\TestCase;
use Monolog\Logger;
class SlackbotHandlerTest extends TestCase
{
    public function testConstructorMinimal()
    {
        $handler = new SlackbotHandler('test-team', 'test-token', 'test-channel');
        $this->assertInstanceOf('Monolog\Handler\AbstractProcessingHandler', $handler);
    }
    public function testConstructorFull()
    {
        $handler = new SlackbotHandler(
            'test-team',
            'test-token',
            'test-channel',
            Logger::DEBUG,
            false
        );
        $this->assertInstanceOf('Monolog\Handler\AbstractProcessingHandler', $handler);
    }
}
