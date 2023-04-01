<?php
namespace Monolog\Processor;
use Monolog\TestCase;
class MemoryPeakUsageProcessorTest extends TestCase
{
    public function testProcessor()
    {
        $processor = new MemoryPeakUsageProcessor();
        $record = $processor($this->getRecord());
        $this->assertArrayHasKey('memory_peak_usage', $record['extra']);
        $this->assertRegExp('#[0-9.]+ (M|K)?B$#', $record['extra']['memory_peak_usage']);
    }
    public function testProcessorWithoutFormatting()
    {
        $processor = new MemoryPeakUsageProcessor(true, false);
        $record = $processor($this->getRecord());
        $this->assertArrayHasKey('memory_peak_usage', $record['extra']);
        $this->assertInternalType('int', $record['extra']['memory_peak_usage']);
        $this->assertGreaterThan(0, $record['extra']['memory_peak_usage']);
    }
}
