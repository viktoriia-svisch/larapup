<?php
namespace Monolog\Formatter;
use Monolog\Logger;
class LogstashFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        \PHPUnit_Framework_Error_Warning::$enabled = true;
        return parent::tearDown();
    }
    public function testDefaultFormatter()
    {
        $formatter = new LogstashFormatter('test', 'hostname');
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array(),
            'datetime' => new \DateTime("@0"),
            'extra' => array(),
            'message' => 'log',
        );
        $message = json_decode($formatter->format($record), true);
        $this->assertEquals("1970-01-01T00:00:00.000000+00:00", $message['@timestamp']);
        $this->assertEquals('log', $message['@message']);
        $this->assertEquals('meh', $message['@fields']['channel']);
        $this->assertContains('meh', $message['@tags']);
        $this->assertEquals(Logger::ERROR, $message['@fields']['level']);
        $this->assertEquals('test', $message['@type']);
        $this->assertEquals('hostname', $message['@source']);
        $formatter = new LogstashFormatter('mysystem');
        $message = json_decode($formatter->format($record), true);
        $this->assertEquals('mysystem', $message['@type']);
    }
    public function testFormatWithFileAndLine()
    {
        $formatter = new LogstashFormatter('test');
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('file' => 'test', 'line' => 14),
            'message' => 'log',
        );
        $message = json_decode($formatter->format($record), true);
        $this->assertEquals('test', $message['@fields']['file']);
        $this->assertEquals(14, $message['@fields']['line']);
    }
    public function testFormatWithContext()
    {
        $formatter = new LogstashFormatter('test');
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('key' => 'pair'),
            'message' => 'log',
        );
        $message = json_decode($formatter->format($record), true);
        $message_array = $message['@fields'];
        $this->assertArrayHasKey('ctxt_from', $message_array);
        $this->assertEquals('logger', $message_array['ctxt_from']);
        $formatter = new LogstashFormatter('test', null, null, 'CTX');
        $message = json_decode($formatter->format($record), true);
        $message_array = $message['@fields'];
        $this->assertArrayHasKey('CTXfrom', $message_array);
        $this->assertEquals('logger', $message_array['CTXfrom']);
    }
    public function testFormatWithExtra()
    {
        $formatter = new LogstashFormatter('test');
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('key' => 'pair'),
            'message' => 'log',
        );
        $message = json_decode($formatter->format($record), true);
        $message_array = $message['@fields'];
        $this->assertArrayHasKey('key', $message_array);
        $this->assertEquals('pair', $message_array['key']);
        $formatter = new LogstashFormatter('test', null, 'EXT');
        $message = json_decode($formatter->format($record), true);
        $message_array = $message['@fields'];
        $this->assertArrayHasKey('EXTkey', $message_array);
        $this->assertEquals('pair', $message_array['EXTkey']);
    }
    public function testFormatWithApplicationName()
    {
        $formatter = new LogstashFormatter('app', 'test');
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('key' => 'pair'),
            'message' => 'log',
        );
        $message = json_decode($formatter->format($record), true);
        $this->assertArrayHasKey('@type', $message);
        $this->assertEquals('app', $message['@type']);
    }
    public function testDefaultFormatterV1()
    {
        $formatter = new LogstashFormatter('test', 'hostname', null, 'ctxt_', LogstashFormatter::V1);
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array(),
            'datetime' => new \DateTime("@0"),
            'extra' => array(),
            'message' => 'log',
        );
        $message = json_decode($formatter->format($record), true);
        $this->assertEquals("1970-01-01T00:00:00.000000+00:00", $message['@timestamp']);
        $this->assertEquals("1", $message['@version']);
        $this->assertEquals('log', $message['message']);
        $this->assertEquals('meh', $message['channel']);
        $this->assertEquals('ERROR', $message['level']);
        $this->assertEquals('test', $message['type']);
        $this->assertEquals('hostname', $message['host']);
        $formatter = new LogstashFormatter('mysystem', null, null, 'ctxt_', LogstashFormatter::V1);
        $message = json_decode($formatter->format($record), true);
        $this->assertEquals('mysystem', $message['type']);
    }
    public function testFormatWithFileAndLineV1()
    {
        $formatter = new LogstashFormatter('test', null, null, 'ctxt_', LogstashFormatter::V1);
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('file' => 'test', 'line' => 14),
            'message' => 'log',
        );
        $message = json_decode($formatter->format($record), true);
        $this->assertEquals('test', $message['file']);
        $this->assertEquals(14, $message['line']);
    }
    public function testFormatWithContextV1()
    {
        $formatter = new LogstashFormatter('test', null, null, 'ctxt_', LogstashFormatter::V1);
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('key' => 'pair'),
            'message' => 'log',
        );
        $message = json_decode($formatter->format($record), true);
        $this->assertArrayHasKey('ctxt_from', $message);
        $this->assertEquals('logger', $message['ctxt_from']);
        $formatter = new LogstashFormatter('test', null, null, 'CTX', LogstashFormatter::V1);
        $message = json_decode($formatter->format($record), true);
        $this->assertArrayHasKey('CTXfrom', $message);
        $this->assertEquals('logger', $message['CTXfrom']);
    }
    public function testFormatWithExtraV1()
    {
        $formatter = new LogstashFormatter('test', null, null, 'ctxt_', LogstashFormatter::V1);
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('key' => 'pair'),
            'message' => 'log',
        );
        $message = json_decode($formatter->format($record), true);
        $this->assertArrayHasKey('key', $message);
        $this->assertEquals('pair', $message['key']);
        $formatter = new LogstashFormatter('test', null, 'EXT', 'ctxt_', LogstashFormatter::V1);
        $message = json_decode($formatter->format($record), true);
        $this->assertArrayHasKey('EXTkey', $message);
        $this->assertEquals('pair', $message['EXTkey']);
    }
    public function testFormatWithApplicationNameV1()
    {
        $formatter = new LogstashFormatter('app', 'test', null, 'ctxt_', LogstashFormatter::V1);
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('from' => 'logger'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('key' => 'pair'),
            'message' => 'log',
        );
        $message = json_decode($formatter->format($record), true);
        $this->assertArrayHasKey('type', $message);
        $this->assertEquals('app', $message['type']);
    }
    public function testFormatWithLatin9Data()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            \PHPUnit_Framework_Error_Warning::$enabled = false;
        }
        $formatter = new LogstashFormatter('test', 'hostname');
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => '¯\_(ツ)_/¯',
            'context' => array(),
            'datetime' => new \DateTime("@0"),
            'extra' => array(
                'user_agent' => "\xD6WN; FBCR/OrangeEspa\xF1a; Vers\xE3o/4.0; F\xE4rist",
            ),
            'message' => 'log',
        );
        $message = json_decode($formatter->format($record), true);
        $this->assertEquals("1970-01-01T00:00:00.000000+00:00", $message['@timestamp']);
        $this->assertEquals('log', $message['@message']);
        $this->assertEquals('¯\_(ツ)_/¯', $message['@fields']['channel']);
        $this->assertContains('¯\_(ツ)_/¯', $message['@tags']);
        $this->assertEquals(Logger::ERROR, $message['@fields']['level']);
        $this->assertEquals('test', $message['@type']);
        $this->assertEquals('hostname', $message['@source']);
        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $this->assertEquals('ÖWN; FBCR/OrangeEspaña; Versão/4.0; Färist', $message['@fields']['user_agent']);
        } else {
            $this->assertEquals(null, $message['@fields']['user_agent']);
        }
    }
}
