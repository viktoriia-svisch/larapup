<?php
namespace Monolog\Processor;
use Monolog\TestCase;
class UidProcessorTest extends TestCase
{
    public function testProcessor()
    {
        $processor = new UidProcessor();
        $record = $processor($this->getRecord());
        $this->assertArrayHasKey('uid', $record['extra']);
    }
    public function testGetUid()
    {
        $processor = new UidProcessor(10);
        $this->assertEquals(10, strlen($processor->getUid()));
    }
}
