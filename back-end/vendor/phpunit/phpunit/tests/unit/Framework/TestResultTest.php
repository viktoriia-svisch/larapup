<?php
namespace PHPUnit\Framework;
class TestResultTest extends TestCase
{
    public function testRemoveListenerRemovesOnlyExpectedListener(): void
    {
        $result         = new TestResult;
        $firstListener  = $this->getMockBuilder(TestListener::class)->getMock();
        $secondListener = $this->getMockBuilder(TestListener::class)->getMock();
        $thirdListener  = $this->getMockBuilder(TestListener::class)->getMock();
        $result->addListener($firstListener);
        $result->addListener($secondListener);
        $result->addListener($thirdListener);
        $result->addListener($firstListener);
        $this->assertAttributeEquals(
            [$firstListener, $secondListener, $thirdListener, $firstListener],
            'listeners',
            $result
        );
        $result->removeListener($firstListener);
        $this->assertAttributeEquals(
            [1 => $secondListener, 2 => $thirdListener],
            'listeners',
            $result
        );
    }
    public function testAddErrorOfTypeIncompleteTest(): void
    {
        $time      = 17;
        $throwable = new IncompleteTestError;
        $result    = new TestResult;
        $test      = $this->getMockBuilder(Test::class)->getMock();
        $listener  = $this->getMockBuilder(TestListener::class)->getMock();
        $listener->expects($this->exactly(2))
            ->method('addIncompleteTest')
            ->with($test, $throwable, $time);
        $result->addListener($listener);
        $result->stopOnIncomplete(false);
        $result->addError($test, $throwable, $time);
        $this->assertAttributeEquals($time, 'time', $result);
        $this->assertAttributeCount(1, 'notImplemented', $result);
        $this->assertAttributeEquals(false, 'stop', $result);
        $result->stopOnIncomplete(true);
        $result->addError($test, $throwable, $time);
        $this->assertAttributeEquals(2 * $time, 'time', $result);
        $this->assertAttributeCount(2, 'notImplemented', $result);
        $this->assertAttributeEquals(true, 'stop', $result);
        $this->assertAttributeEquals(true, 'lastTestFailed', $result);
        $this->assertAttributeContainsOnly(TestFailure::class, 'notImplemented', $result);
    }
    public function canSkipCoverageProvider(): array
    {
        return [
            ['CoverageClassTest', true],
            ['CoverageNothingTest', true],
            ['CoverageCoversOverridesCoversNothingTest', false],
        ];
    }
    public function testCanSkipCoverage($testCase, $expectedCanSkip): void
    {
        require_once TEST_FILES_PATH . $testCase . '.php';
        $test            = new $testCase;
        $canSkipCoverage = TestResult::isAnyCoverageRequired($test);
        $this->assertEquals($expectedCanSkip, $canSkipCoverage);
    }
}
