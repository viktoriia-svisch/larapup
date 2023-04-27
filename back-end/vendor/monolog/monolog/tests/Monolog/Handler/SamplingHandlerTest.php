<?php
namespace Monolog\Handler;
use Monolog\TestCase;
class SamplingHandlerTest extends TestCase
{
    public function testHandle()
    {
        $testHandler = new TestHandler();
        $handler = new SamplingHandler($testHandler, 2);
        for ($i = 0; $i < 10000; $i++) {
            $handler->handle($this->getRecord());
        }
        $count = count($testHandler->getRecords());
        $this->assertLessThan(6000, $count);
        $this->assertGreaterThan(4000, $count);
    }
}
