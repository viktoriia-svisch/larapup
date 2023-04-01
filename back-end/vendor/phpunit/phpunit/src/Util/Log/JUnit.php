<?php
namespace PHPUnit\Util\Log;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Util\Filter;
use PHPUnit\Util\Printer;
use PHPUnit\Util\Xml;
use ReflectionClass;
use ReflectionException;
class JUnit extends Printer implements TestListener
{
    protected $document;
    protected $root;
    protected $reportUselessTests = false;
    protected $writeDocument = true;
    protected $testSuites = [];
    protected $testSuiteTests = [0];
    protected $testSuiteAssertions = [0];
    protected $testSuiteErrors = [0];
    protected $testSuiteFailures = [0];
    protected $testSuiteSkipped = [0];
    protected $testSuiteTimes = [0];
    protected $testSuiteLevel = 0;
    protected $currentTestCase;
    public function __construct($out = null, bool $reportUselessTests = false)
    {
        $this->document               = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;
        $this->root = $this->document->createElement('testsuites');
        $this->document->appendChild($this->root);
        parent::__construct($out);
        $this->reportUselessTests = $reportUselessTests;
    }
    public function flush(): void
    {
        if ($this->writeDocument === true) {
            $this->write($this->getXML());
        }
        parent::flush();
    }
    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->doAddFault($test, $t, $time, 'error');
        $this->testSuiteErrors[$this->testSuiteLevel]++;
    }
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->doAddFault($test, $e, $time, 'warning');
        $this->testSuiteFailures[$this->testSuiteLevel]++;
    }
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->doAddFault($test, $e, $time, 'failure');
        $this->testSuiteFailures[$this->testSuiteLevel]++;
    }
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        $this->doAddSkipped($test);
    }
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        if (!$this->reportUselessTests || $this->currentTestCase === null) {
            return;
        }
        $error = $this->document->createElement(
            'error',
            Xml::prepareString(
                "Risky Test\n" .
                Filter::getFilteredStacktrace($t)
            )
        );
        $error->setAttribute('type', \get_class($t));
        $this->currentTestCase->appendChild($error);
        $this->testSuiteErrors[$this->testSuiteLevel]++;
    }
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        $this->doAddSkipped($test);
    }
    public function startTestSuite(TestSuite $suite): void
    {
        $testSuite = $this->document->createElement('testsuite');
        $testSuite->setAttribute('name', $suite->getName());
        if (\class_exists($suite->getName(), false)) {
            try {
                $class = new ReflectionClass($suite->getName());
                $testSuite->setAttribute('file', $class->getFileName());
            } catch (ReflectionException $e) {
            }
        }
        if ($this->testSuiteLevel > 0) {
            $this->testSuites[$this->testSuiteLevel]->appendChild($testSuite);
        } else {
            $this->root->appendChild($testSuite);
        }
        $this->testSuiteLevel++;
        $this->testSuites[$this->testSuiteLevel]          = $testSuite;
        $this->testSuiteTests[$this->testSuiteLevel]      = 0;
        $this->testSuiteAssertions[$this->testSuiteLevel] = 0;
        $this->testSuiteErrors[$this->testSuiteLevel]     = 0;
        $this->testSuiteFailures[$this->testSuiteLevel]   = 0;
        $this->testSuiteSkipped[$this->testSuiteLevel]    = 0;
        $this->testSuiteTimes[$this->testSuiteLevel]      = 0;
    }
    public function endTestSuite(TestSuite $suite): void
    {
        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'tests',
            $this->testSuiteTests[$this->testSuiteLevel]
        );
        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'assertions',
            $this->testSuiteAssertions[$this->testSuiteLevel]
        );
        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'errors',
            $this->testSuiteErrors[$this->testSuiteLevel]
        );
        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'failures',
            $this->testSuiteFailures[$this->testSuiteLevel]
        );
        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'skipped',
            $this->testSuiteSkipped[$this->testSuiteLevel]
        );
        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'time',
            \sprintf('%F', $this->testSuiteTimes[$this->testSuiteLevel])
        );
        if ($this->testSuiteLevel > 1) {
            $this->testSuiteTests[$this->testSuiteLevel - 1] += $this->testSuiteTests[$this->testSuiteLevel];
            $this->testSuiteAssertions[$this->testSuiteLevel - 1] += $this->testSuiteAssertions[$this->testSuiteLevel];
            $this->testSuiteErrors[$this->testSuiteLevel - 1] += $this->testSuiteErrors[$this->testSuiteLevel];
            $this->testSuiteFailures[$this->testSuiteLevel - 1] += $this->testSuiteFailures[$this->testSuiteLevel];
            $this->testSuiteSkipped[$this->testSuiteLevel - 1] += $this->testSuiteSkipped[$this->testSuiteLevel];
            $this->testSuiteTimes[$this->testSuiteLevel - 1] += $this->testSuiteTimes[$this->testSuiteLevel];
        }
        $this->testSuiteLevel--;
    }
    public function startTest(Test $test): void
    {
        $usesDataprovider = false;
        if (\method_exists($test, 'usesDataProvider')) {
            $usesDataprovider = $test->usesDataProvider();
        }
        $testCase = $this->document->createElement('testcase');
        $testCase->setAttribute('name', $test->getName());
        $class      = new ReflectionClass($test);
        $methodName = $test->getName(!$usesDataprovider);
        if ($class->hasMethod($methodName)) {
            $method = $class->getMethod($methodName);
            $testCase->setAttribute('class', $class->getName());
            $testCase->setAttribute('classname', \str_replace('\\', '.', $class->getName()));
            $testCase->setAttribute('file', $class->getFileName());
            $testCase->setAttribute('line', $method->getStartLine());
        }
        $this->currentTestCase = $testCase;
    }
    public function endTest(Test $test, float $time): void
    {
        $numAssertions = 0;
        if (\method_exists($test, 'getNumAssertions')) {
            $numAssertions = $test->getNumAssertions();
        }
        $this->testSuiteAssertions[$this->testSuiteLevel] += $numAssertions;
        $this->currentTestCase->setAttribute(
            'assertions',
            $numAssertions
        );
        $this->currentTestCase->setAttribute(
            'time',
            \sprintf('%F', $time)
        );
        $this->testSuites[$this->testSuiteLevel]->appendChild(
            $this->currentTestCase
        );
        $this->testSuiteTests[$this->testSuiteLevel]++;
        $this->testSuiteTimes[$this->testSuiteLevel] += $time;
        $testOutput = '';
        if (\method_exists($test, 'hasOutput') && \method_exists($test, 'getActualOutput')) {
            $testOutput = $test->hasOutput() ? $test->getActualOutput() : '';
        }
        if (!empty($testOutput)) {
            $systemOut = $this->document->createElement(
                'system-out',
                Xml::prepareString($testOutput)
            );
            $this->currentTestCase->appendChild($systemOut);
        }
        $this->currentTestCase = null;
    }
    public function getXML(): string
    {
        return $this->document->saveXML();
    }
    public function setWriteDocument( $flag): void
    {
        if (\is_bool($flag)) {
            $this->writeDocument = $flag;
        }
    }
    private function doAddFault(Test $test, \Throwable $t, float $time, $type): void
    {
        if ($this->currentTestCase === null) {
            return;
        }
        if ($test instanceof SelfDescribing) {
            $buffer = $test->toString() . "\n";
        } else {
            $buffer = '';
        }
        $buffer .= TestFailure::exceptionToString($t) . "\n" .
                   Filter::getFilteredStacktrace($t);
        $fault = $this->document->createElement(
            $type,
            Xml::prepareString($buffer)
        );
        if ($t instanceof ExceptionWrapper) {
            $fault->setAttribute('type', $t->getClassName());
        } else {
            $fault->setAttribute('type', \get_class($t));
        }
        $this->currentTestCase->appendChild($fault);
    }
    private function doAddSkipped(Test $test): void
    {
        if ($this->currentTestCase === null) {
            return;
        }
        $skipped = $this->document->createElement('skipped');
        $this->currentTestCase->appendChild($skipped);
        $this->testSuiteSkipped[$this->testSuiteLevel]++;
    }
}
