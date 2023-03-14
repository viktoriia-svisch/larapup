<?php
namespace tests\Mockery\Adapter\Phpunit;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\TestResult;
use Mockery\Adapter\Phpunit\TestListener;
if (class_exists('PHPUnit_Runner_Version') && version_compare(\PHPUnit_Runner_Version::id(), '6.0.0', '<')) {
    class_alias('test\Mockery\Fixtures\EmptyTestCaseV5', 'tests\Mockery\Adapter\Phpunit\EmptyTestCase');
} elseif (version_compare(\PHPUnit\Runner\Version::id(), '7.0.0', '<')) {
    class_alias('test\Mockery\Fixtures\EmptyTestCaseV6', 'tests\Mockery\Adapter\Phpunit\EmptyTestCase');
} else {
    class_alias('test\Mockery\Fixtures\EmptyTestCaseV7', 'tests\Mockery\Adapter\Phpunit\EmptyTestCase');
}
class TestListenerTest extends MockeryTestCase
{
    protected function mockeryTestSetUp()
    {
        if (class_exists('\PHPUnit\Runner\Version')) {
            $ver = \PHPUnit\Runner\Version::series();
        } else {
            $ver = \PHPUnit_Runner_Version::series();
        }
        if (intval($ver) < 6) {
            $this->markTestSkipped('The TestListener is only supported with PHPUnit 6+.');
            return;
        }
        $this->container = \Mockery::getContainer();
        $this->listener = new TestListener();
        $this->testResult = new TestResult();
        $this->test = new EmptyTestCase();
        $this->test->setTestResultObject($this->testResult);
        $this->testResult->addListener($this->listener);
        $this->assertTrue($this->testResult->wasSuccessful(), 'sanity check: empty test results should be considered successful');
    }
    public function testSuccessOnClose()
    {
        $mock = $this->container->mock();
        $mock->shouldReceive('bar')->once();
        $mock->bar();
        $this->test->addToAssertionCount($this->container->mockery_getExpectationCount());
        \Mockery::close();
        $this->listener->endTest($this->test, 0);
        $this->assertTrue($this->testResult->wasSuccessful(), 'expected test result to indicate success');
    }
    public function testFailureOnMissingClose()
    {
        $mock = $this->container->mock();
        $mock->shouldReceive('bar')->once();
        $this->listener->endTest($this->test, 0);
        $this->assertFalse($this->testResult->wasSuccessful(), 'expected test result to indicate failure');
        $mock->bar();
        \Mockery::close();
    }
    public function testMockeryIsAddedToBlacklist()
    {
        $suite = \Mockery::mock(\PHPUnit\Framework\TestSuite::class);
        $this->assertArrayNotHasKey(\Mockery::class, \PHPUnit\Util\Blacklist::$blacklistedClassNames);
        $this->listener->startTestSuite($suite);
        $this->assertSame(1, \PHPUnit\Util\Blacklist::$blacklistedClassNames[\Mockery::class]);
    }
}
