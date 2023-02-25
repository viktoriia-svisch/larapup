<?php
namespace Monolog\Handler;
use Monolog\Logger;
class SyslogHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $handler = new SyslogHandler('test');
        $this->assertInstanceOf('Monolog\Handler\SyslogHandler', $handler);
        $handler = new SyslogHandler('test', LOG_USER);
        $this->assertInstanceOf('Monolog\Handler\SyslogHandler', $handler);
        $handler = new SyslogHandler('test', 'user');
        $this->assertInstanceOf('Monolog\Handler\SyslogHandler', $handler);
        $handler = new SyslogHandler('test', LOG_USER, Logger::DEBUG, true, LOG_PERROR);
        $this->assertInstanceOf('Monolog\Handler\SyslogHandler', $handler);
    }
    public function testConstructInvalidFacility()
    {
        $this->setExpectedException('UnexpectedValueException');
        $handler = new SyslogHandler('test', 'unknown');
    }
}
