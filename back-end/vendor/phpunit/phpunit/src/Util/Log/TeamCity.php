<?php
namespace PHPUnit\Util\Log;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\TextUI\ResultPrinter;
use PHPUnit\Util\Filter;
use ReflectionClass;
use SebastianBergmann\Comparator\ComparisonFailure;
class TeamCity extends ResultPrinter
{
    private $isSummaryTestCountPrinted = false;
    private $startedTestName;
    private $flowId;
    public function printResult(TestResult $result): void
    {
        $this->printHeader();
        $this->printFooter($result);
    }
    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->printEvent(
            'testFailed',
            [
                'name'     => $test->getName(),
                'message'  => self::getMessage($t),
                'details'  => self::getDetails($t),
                'duration' => self::toMilliseconds($time),
            ]
        );
    }
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->printEvent(
            'testFailed',
            [
                'name'     => $test->getName(),
                'message'  => self::getMessage($e),
                'details'  => self::getDetails($e),
                'duration' => self::toMilliseconds($time),
            ]
        );
    }
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $parameters = [
            'name'     => $test->getName(),
            'message'  => self::getMessage($e),
            'details'  => self::getDetails($e),
            'duration' => self::toMilliseconds($time),
        ];
        if ($e instanceof ExpectationFailedException) {
            $comparisonFailure = $e->getComparisonFailure();
            if ($comparisonFailure instanceof ComparisonFailure) {
                $expectedString = $comparisonFailure->getExpectedAsString();
                if ($expectedString === null || empty($expectedString)) {
                    $expectedString = self::getPrimitiveValueAsString($comparisonFailure->getExpected());
                }
                $actualString = $comparisonFailure->getActualAsString();
                if ($actualString === null || empty($actualString)) {
                    $actualString = self::getPrimitiveValueAsString($comparisonFailure->getActual());
                }
                if ($actualString !== null && $expectedString !== null) {
                    $parameters['type']     = 'comparisonFailure';
                    $parameters['actual']   = $actualString;
                    $parameters['expected'] = $expectedString;
                }
            }
        }
        $this->printEvent('testFailed', $parameters);
    }
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        $this->printIgnoredTest($test->getName(), $t, $time);
    }
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        $this->addError($test, $t, $time);
    }
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        $testName = $test->getName();
        if ($this->startedTestName !== $testName) {
            $this->startTest($test);
            $this->printIgnoredTest($testName, $t, $time);
            $this->endTest($test, $time);
        } else {
            $this->printIgnoredTest($testName, $t, $time);
        }
    }
    public function printIgnoredTest($testName, \Throwable $t, float $time): void
    {
        $this->printEvent(
            'testIgnored',
            [
                'name'     => $testName,
                'message'  => self::getMessage($t),
                'details'  => self::getDetails($t),
                'duration' => self::toMilliseconds($time),
            ]
        );
    }
    public function startTestSuite(TestSuite $suite): void
    {
        if (\stripos(\ini_get('disable_functions'), 'getmypid') === false) {
            $this->flowId = \getmypid();
        } else {
            $this->flowId = false;
        }
        if (!$this->isSummaryTestCountPrinted) {
            $this->isSummaryTestCountPrinted = true;
            $this->printEvent(
                'testCount',
                ['count' => \count($suite)]
            );
        }
        $suiteName = $suite->getName();
        if (empty($suiteName)) {
            return;
        }
        $parameters = ['name' => $suiteName];
        if (\class_exists($suiteName, false)) {
            $fileName                   = self::getFileName($suiteName);
            $parameters['locationHint'] = "php_qn:
        } else {
            $split = \explode('::', $suiteName);
            if (\count($split) === 2 && \method_exists($split[0], $split[1])) {
                $fileName                   = self::getFileName($split[0]);
                $parameters['locationHint'] = "php_qn:
                $parameters['name']         = $split[1];
            }
        }
        $this->printEvent('testSuiteStarted', $parameters);
    }
    public function endTestSuite(TestSuite $suite): void
    {
        $suiteName = $suite->getName();
        if (empty($suiteName)) {
            return;
        }
        $parameters = ['name' => $suiteName];
        if (!\class_exists($suiteName, false)) {
            $split = \explode('::', $suiteName);
            if (\count($split) === 2 && \method_exists($split[0], $split[1])) {
                $parameters['name'] = $split[1];
            }
        }
        $this->printEvent('testSuiteFinished', $parameters);
    }
    public function startTest(Test $test): void
    {
        $testName              = $test->getName();
        $this->startedTestName = $testName;
        $params                = ['name' => $testName];
        if ($test instanceof TestCase) {
            $className              = \get_class($test);
            $fileName               = self::getFileName($className);
            $params['locationHint'] = "php_qn:
        }
        $this->printEvent('testStarted', $params);
    }
    public function endTest(Test $test, float $time): void
    {
        parent::endTest($test, $time);
        $this->printEvent(
            'testFinished',
            [
                'name'     => $test->getName(),
                'duration' => self::toMilliseconds($time),
            ]
        );
    }
    protected function writeProgress(string $progress): void
    {
    }
    private function printEvent($eventName, $params = []): void
    {
        $this->write("\n##teamcity[$eventName");
        if ($this->flowId) {
            $params['flowId'] = $this->flowId;
        }
        foreach ($params as $key => $value) {
            $escapedValue = self::escapeValue($value);
            $this->write(" $key='$escapedValue'");
        }
        $this->write("]\n");
    }
    private static function getMessage(\Throwable $t): string
    {
        $message = '';
        if ($t instanceof ExceptionWrapper) {
            if ($t->getClassName() !== '') {
                $message .= $t->getClassName();
            }
            if ($message !== '' && $t->getMessage() !== '') {
                $message .= ' : ';
            }
        }
        return $message . $t->getMessage();
    }
    private static function getDetails(\Throwable $t): string
    {
        $stackTrace = Filter::getFilteredStacktrace($t);
        $previous   = $t instanceof ExceptionWrapper ? $t->getPreviousWrapped() : $t->getPrevious();
        while ($previous) {
            $stackTrace .= "\nCaused by\n" .
                TestFailure::exceptionToString($previous) . "\n" .
                Filter::getFilteredStacktrace($previous);
            $previous = $previous instanceof ExceptionWrapper ?
                $previous->getPreviousWrapped() : $previous->getPrevious();
        }
        return ' ' . \str_replace("\n", "\n ", $stackTrace);
    }
    private static function getPrimitiveValueAsString($value): ?string
    {
        if ($value === null) {
            return 'null';
        }
        if (\is_bool($value)) {
            return $value === true ? 'true' : 'false';
        }
        if (\is_scalar($value)) {
            return \print_r($value, true);
        }
        return null;
    }
    private static function escapeValue(string $text): string
    {
        return \str_replace(
            ['|', "'", "\n", "\r", ']', '['],
            ['||', "|'", '|n', '|r', '|]', '|['],
            $text
        );
    }
    private static function getFileName($className): string
    {
        $reflectionClass = new ReflectionClass($className);
        return $reflectionClass->getFileName();
    }
    private static function toMilliseconds(float $time): int
    {
        return \round($time * 1000);
    }
}
