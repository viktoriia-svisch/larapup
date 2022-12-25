<?php
namespace Monolog\Processor;
class PsrLogMessageProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testReplacement($val, $expected)
    {
        $proc = new PsrLogMessageProcessor;
        $message = $proc(array(
            'message' => '{foo}',
            'context' => array('foo' => $val),
        ));
        $this->assertEquals($expected, $message['message']);
    }
    public function getPairs()
    {
        return array(
            array('foo',    'foo'),
            array('3',      '3'),
            array(3,        '3'),
            array(null,     ''),
            array(true,     '1'),
            array(false,    ''),
            array(new \stdClass, '[object stdClass]'),
            array(array(), '[array]'),
        );
    }
}
