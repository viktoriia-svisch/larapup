<?php
namespace Mockery\Adapter\Phpunit\Legacy;
if (class_exists('PHPUnit_Framework_TestCase') && ! class_exists('PHPUnit\Util\Blacklist')) {
    class_alias('PHPUnit_Framework_ExpectationFailedException', 'PHPUnit\Framework\ExpectationFailedException');
    class_alias('PHPUnit_Framework_Test', 'PHPUnit\Framework\Test');
    class_alias('PHPUnit_Framework_TestCase', 'PHPUnit\Framework\TestCase');
    class_alias('PHPUnit_Util_Blacklist', 'PHPUnit\Util\Blacklist');
    class_alias('PHPUnit_Runner_BaseTestRunner', 'PHPUnit\Runner\BaseTestRunner');
}
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Blacklist;
use PHPUnit\Runner\BaseTestRunner;
class TestListenerTrait
{
    public function endTest(Test $test, $time)
    {
        if (!$test instanceof TestCase) {
            return;
        }
        if ($test->getStatus() !== BaseTestRunner::STATUS_PASSED) {
            return;
        }
        try {
            \Mockery::self();
        } catch (\LogicException $_) {
            return;
        }
        $e = new ExpectationFailedException(
            \sprintf(
                "Mockery's expectations have not been verified. Make sure that \Mockery::close() is called at the end of the test. Consider using %s\MockeryPHPUnitIntegration or extending %s\MockeryTestCase.",
                __NAMESPACE__,
                __NAMESPACE__
            )
        );
        $result = $test->getTestResultObject();
        if ($result !== null) {
            $result->addFailure($test, $e, $time);
        }
    }
    public function startTestSuite()
    {
        Blacklist::$blacklistedClassNames[\Mockery::class] = 1;
    }
}
