<?php
namespace Monolog\Processor;
use Monolog\TestCase;
class TagProcessorTest extends TestCase
{
    public function testProcessor()
    {
        $tags = array(1, 2, 3);
        $processor = new TagProcessor($tags);
        $record = $processor($this->getRecord());
        $this->assertEquals($tags, $record['extra']['tags']);
    }
    public function testProcessorTagModification()
    {
        $tags = array(1, 2, 3);
        $processor = new TagProcessor($tags);
        $record = $processor($this->getRecord());
        $this->assertEquals($tags, $record['extra']['tags']);
        $processor->setTags(array('a', 'b'));
        $record = $processor($this->getRecord());
        $this->assertEquals(array('a', 'b'), $record['extra']['tags']);
        $processor->addTags(array('a', 'c', 'foo' => 'bar'));
        $record = $processor($this->getRecord());
        $this->assertEquals(array('a', 'b', 'a', 'c', 'foo' => 'bar'), $record['extra']['tags']);
    }
}
