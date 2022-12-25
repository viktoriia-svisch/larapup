<?php
namespace Symfony\Polyfill\Util;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener as TestListenerInterface;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\WarningTestCase;
if (class_exists('PHPUnit_Runner_Version') && version_compare(\PHPUnit_Runner_Version::id(), '6.0.0', '<')) {
    class_alias('Symfony\Polyfill\Util\LegacyTestListener', 'Symfony\Polyfill\Util\TestListener');
} else {
    class TestListener extends TestSuite implements TestListenerInterface
    {
        private $suite;
        private $trait;
        public function __construct(TestSuite $suite = null)
        {
            if ($suite) {
                $this->suite = $suite;
                $this->setName($suite->getName().' with polyfills enabled');
                $this->addTest($suite);
            }
            $this->trait = new TestListenerTrait();
        }
        public function startTestSuite(TestSuite $suite)
        {
            $this->trait->startTestSuite($suite);
        }
        protected function setUp()
        {
            TestListenerTrait::$enabledPolyfills = $this->suite->getName();
        }
        protected function tearDown()
        {
            TestListenerTrait::$enabledPolyfills = false;
        }
        public function addError(Test $test, \Exception $e, $time)
        {
            $this->trait->addError($test, $e, $time);
        }
        public function addWarning(Test $test, Warning $e, $time)
        {
        }
        public function addFailure(Test $test, AssertionFailedError $e, $time)
        {
            $this->trait->addError($test, $e, $time);
        }
        public function addIncompleteTest(Test $test, \Exception $e, $time)
        {
        }
        public function addRiskyTest(Test $test, \Exception $e, $time)
        {
        }
        public function addSkippedTest(Test $test, \Exception $e, $time)
        {
        }
        public function endTestSuite(TestSuite $suite)
        {
        }
        public function startTest(Test $test)
        {
        }
        public function endTest(Test $test, $time)
        {
        }
        public static function warning($message)
        {
            return parent::warning($message);
        }
    }
}
