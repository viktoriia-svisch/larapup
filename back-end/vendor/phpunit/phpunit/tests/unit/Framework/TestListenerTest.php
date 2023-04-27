<?php
namespace PHPUnit\Framework;
use MyTestListener;
final class TestListenerTest extends TestCase
{
    private $result;
    private $listener;
    protected function setUp(): void
    {
        $this->result   = new TestResult;
        $this->listener = new MyTestListener;
        $this->result->addListener($this->listener);
    }
    public function testError(): void
    {
        $test = new \TestError;
        $test->run($this->result);
        $this->assertEquals(1, $this->listener->errorCount());
        $this->assertEquals(1, $this->listener->endCount());
    }
    public function testFailure(): void
    {
        $test = new \Failure;
        $test->run($this->result);
        $this->assertEquals(1, $this->listener->failureCount());
        $this->assertEquals(1, $this->listener->endCount());
    }
    public function testStartStop(): void
    {
        $test = new \Success;
        $test->run($this->result);
        $this->assertEquals(1, $this->listener->startCount());
        $this->assertEquals(1, $this->listener->endCount());
    }
}
