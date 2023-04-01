<?php
namespace Monolog\Processor;
use Monolog\TestCase;
class GitProcessorTest extends TestCase
{
    public function testProcessor()
    {
        $processor = new GitProcessor();
        $record = $processor($this->getRecord());
        $this->assertArrayHasKey('git', $record['extra']);
        $this->assertTrue(!is_array($record['extra']['git']['branch']));
    }
}
