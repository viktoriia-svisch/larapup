<?php
namespace PHPUnit\Framework;
class TestImplementorTest extends TestCase
{
    public function testSuccessfulRun(): void
    {
        $result = new TestResult;
        $test = new \DoubleTestCase(new \Success);
        $test->run($result);
        $this->assertCount(\count($test), $result);
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
    }
}
