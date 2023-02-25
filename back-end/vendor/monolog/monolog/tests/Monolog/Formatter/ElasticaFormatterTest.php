<?php
namespace Monolog\Formatter;
use Monolog\Logger;
class ElasticaFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists("Elastica\Document")) {
            $this->markTestSkipped("ruflin/elastica not installed");
        }
    }
    public function testFormat()
    {
        $msg = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('foo' => 7, 'bar', 'class' => new \stdClass),
            'datetime' => new \DateTime("@0"),
            'extra' => array(),
            'message' => 'log',
        );
        $expected = $msg;
        $expected['datetime'] = '1970-01-01T00:00:00.000000+00:00';
        $expected['context'] = array(
            'class' => '[object] (stdClass: {})',
            'foo' => 7,
            0 => 'bar',
        );
        $formatter = new ElasticaFormatter('my_index', 'doc_type');
        $doc = $formatter->format($msg);
        $this->assertInstanceOf('Elastica\Document', $doc);
        $params = $doc->getParams();
        $this->assertEquals('my_index', $params['_index']);
        $this->assertEquals('doc_type', $params['_type']);
        $data = $doc->getData();
        foreach (array_keys($expected) as $key) {
            $this->assertEquals($expected[$key], $data[$key]);
        }
    }
    public function testGetters()
    {
        $formatter = new ElasticaFormatter('my_index', 'doc_type');
        $this->assertEquals('my_index', $formatter->getIndex());
        $this->assertEquals('doc_type', $formatter->getType());
    }
}
