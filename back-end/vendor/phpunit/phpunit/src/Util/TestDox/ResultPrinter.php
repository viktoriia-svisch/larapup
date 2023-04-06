<?php
namespace PHPUnit\Util\TestDox;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\WarningTestCase;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Util\Printer;
abstract class ResultPrinter extends Printer implements TestListener
{
    protected $prettifier;
    protected $testClass = '';
    protected $testStatus;
    protected $tests = [];
    protected $successful = 0;
    protected $warned = 0;
    protected $failed = 0;
    protected $risky = 0;
    protected $skipped = 0;
    protected $incomplete = 0;
    protected $currentTestClassPrettified;
    protected $currentTestMethodPrettified;
    private $groups;
    private $excludeGroups;
    public function __construct($out = null, array $groups = [], array $excludeGroups = [])
    {
        parent::__construct($out);
        $this->groups        = $groups;
        $this->excludeGroups = $excludeGroups;
        $this->prettifier = new NamePrettifier;
        $this->startRun();
    }
    public function flush(): void
    {
        $this->doEndClass();
        $this->endRun();
        parent::flush();
    }
    public function addError(Test $test, \Throwable $t, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }
        $this->testStatus = BaseTestRunner::STATUS_ERROR;
        $this->failed++;
    }
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }
        $this->testStatus = BaseTestRunner::STATUS_WARNING;
        $this->warned++;
    }
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }
        $this->testStatus = BaseTestRunner::STATUS_FAILURE;
        $this->failed++;
    }
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }
        $this->testStatus = BaseTestRunner::STATUS_INCOMPLETE;
        $this->incomplete++;
    }
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }
        $this->testStatus = BaseTestRunner::STATUS_RISKY;
        $this->risky++;
    }
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }
        $this->testStatus = BaseTestRunner::STATUS_SKIPPED;
        $this->skipped++;
    }
    public function startTestSuite(TestSuite $suite): void
    {
    }
    public function endTestSuite(TestSuite $suite): void
    {
    }
    public function startTest(Test $test): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }
        $class = \get_class($test);
        if ($this->testClass !== $class) {
            if ($this->testClass !== '') {
                $this->doEndClass();
            }
            $this->currentTestClassPrettified = $this->prettifier->prettifyTestClass($class);
            $this->testClass                  = $class;
            $this->tests                      = [];
            $this->startClass($class);
        }
        if ($test instanceof TestCase) {
            $this->currentTestMethodPrettified = $this->prettifier->prettifyTestCase($test);
        }
        $this->testStatus = BaseTestRunner::STATUS_PASSED;
    }
    public function endTest(Test $test, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }
        $this->tests[] = [$this->currentTestMethodPrettified, $this->testStatus];
        $this->currentTestClassPrettified  = null;
        $this->currentTestMethodPrettified = null;
    }
    protected function doEndClass(): void
    {
        foreach ($this->tests as $test) {
            $this->onTest($test[0], $test[1] === BaseTestRunner::STATUS_PASSED);
        }
        $this->endClass($this->testClass);
    }
    protected function startRun(): void
    {
    }
    protected function startClass(string $name): void
    {
    }
    protected function onTest($name, bool $success = true): void
    {
    }
    protected function endClass(string $name): void
    {
    }
    protected function endRun(): void
    {
    }
    private function isOfInterest(Test $test): bool
    {
        if (!$test instanceof TestCase) {
            return false;
        }
        if ($test instanceof WarningTestCase) {
            return false;
        }
        if (!empty($this->groups)) {
            foreach ($test->getGroups() as $group) {
                if (\in_array($group, $this->groups)) {
                    return true;
                }
            }
            return false;
        }
        if (!empty($this->excludeGroups)) {
            foreach ($test->getGroups() as $group) {
                if (\in_array($group, $this->excludeGroups)) {
                    return false;
                }
            }
            return true;
        }
        return true;
    }
}
