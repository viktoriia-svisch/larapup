<?php
namespace Monolog\Formatter;
use Monolog\Logger;
use Monolog\TestCase;
class FlowdockFormatterTest extends TestCase
{
    public function testFormat()
    {
        $formatter = new FlowdockFormatter('test_source', 'source@test.com');
        $record = $this->getRecord();
        $expected = array(
            'source' => 'test_source',
            'from_address' => 'source@test.com',
            'subject' => 'in test_source: WARNING - test',
            'content' => 'test',
            'tags' => array('#logs', '#warning', '#test'),
            'project' => 'test_source',
        );
        $formatted = $formatter->format($record);
        $this->assertEquals($expected, $formatted['flowdock']);
    }
    public function testFormatBatch()
    {
        $formatter = new FlowdockFormatter('test_source', 'source@test.com');
        $records = array(
            $this->getRecord(Logger::WARNING),
            $this->getRecord(Logger::DEBUG),
        );
        $formatted = $formatter->formatBatch($records);
        $this->assertArrayHasKey('flowdock', $formatted[0]);
        $this->assertArrayHasKey('flowdock', $formatted[1]);
    }
}
