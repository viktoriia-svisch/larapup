<?php
namespace Monolog\Processor;
use Monolog\TestCase;
class MemoryUsageProcessorTest extends TestCase
{
    public function testProcessor()
    {
        $processor = new MemoryUsageProcessor();
        $record = $processor($this->getRecord());
        $this->assertArrayHasKey('memory_usage', $record['extra']);
        $this->assertRegExp('#[0-9.]+ (M|K)?B$#', $record['extra']['memory_usage']);
    }
    public function testProcessorWithoutFormatting()
    {
        $processor = new MemoryUsageProcessor(true, false);
        $record = $processor($this->getRecord());
        $this->assertArrayHasKey('memory_usage', $record['extra']);
        $this->assertInternalType('int', $record['extra']['memory_usage']);
        $this->assertGreaterThan(0, $record['extra']['memory_usage']);
    }
}
