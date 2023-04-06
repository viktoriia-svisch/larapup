<?php
namespace Monolog\Handler;
use Monolog\TestCase;
use Monolog\Handler\SyslogUdp\UdpSocket;
class UdpSocketTest extends TestCase
{
    public function testWeDoNotTruncateShortMessages()
    {
        $socket = $this->getMock('\Monolog\Handler\SyslogUdp\UdpSocket', array('send'), array('lol', 'lol'));
        $socket->expects($this->at(0))
            ->method('send')
            ->with("HEADER: The quick brown fox jumps over the lazy dog");
        $socket->write("The quick brown fox jumps over the lazy dog", "HEADER: ");
    }
    public function testLongMessagesAreTruncated()
    {
        $socket = $this->getMock('\Monolog\Handler\SyslogUdp\UdpSocket', array('send'), array('lol', 'lol'));
        $truncatedString = str_repeat("derp", 16254).'d';
        $socket->expects($this->exactly(1))
            ->method('send')
            ->with("HEADER" . $truncatedString);
        $longString = str_repeat("derp", 20000);
        $socket->write($longString, "HEADER");
    }
    public function testDoubleCloseDoesNotError()
    {
        $socket = new UdpSocket('127.0.0.1', 514);
        $socket->close();
        $socket->close();
    }
    public function testWriteAfterCloseErrors()
    {
        $socket = new UdpSocket('127.0.0.1', 514);
        $socket->close();
        $socket->write('foo', "HEADER");
    }
}
