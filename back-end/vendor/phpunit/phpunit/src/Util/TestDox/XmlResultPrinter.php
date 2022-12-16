<?php
namespace PHPUnit\Util\TestDox;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Util\Printer;
use ReflectionClass;
class XmlResultPrinter extends Printer implements TestListener
{
    private $document;
    private $root;
    private $prettifier;
    private $exception;
    public function __construct($out = null)
    {
        $this->document               = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;
        $this->root = $this->document->createElement('tests');
        $this->document->appendChild($this->root);
        $this->prettifier = new NamePrettifier;
        parent::__construct($out);
    }
    public function flush(): void
    {
        $this->write($this->document->saveXML());
        parent::flush();
    }
    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->exception = $t;
    }
    public function addWarning(Test $test, Warning $e, float $time): void
    {
    }
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->exception = $e;
    }
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
    }
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
    }
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
    }
    public function startTestSuite(TestSuite $suite): void
    {
    }
    public function endTestSuite(TestSuite $suite): void
    {
    }
    public function startTest(Test $test): void
    {
        $this->exception = null;
    }
    public function endTest(Test $test, float $time): void
    {
        if (!$test instanceof TestCase) {
            return;
        }
        $groups = \array_filter(
            $test->getGroups(),
            function ($group) {
                return !($group === 'small' || $group === 'medium' || $group === 'large');
            }
        );
        $node = $this->document->createElement('test');
        $node->setAttribute('className', \get_class($test));
        $node->setAttribute('methodName', $test->getName());
        $node->setAttribute('prettifiedClassName', $this->prettifier->prettifyTestClass(\get_class($test)));
        $node->setAttribute('prettifiedMethodName', $this->prettifier->prettifyTestCase($test));
        $node->setAttribute('status', $test->getStatus());
        $node->setAttribute('time', $time);
        $node->setAttribute('size', $test->getSize());
        $node->setAttribute('groups', \implode(',', $groups));
        $inlineAnnotations = \PHPUnit\Util\Test::getInlineAnnotations(\get_class($test), $test->getName());
        if (isset($inlineAnnotations['given'], $inlineAnnotations['when'], $inlineAnnotations['then'])) {
            $node->setAttribute('given', $inlineAnnotations['given']['value']);
            $node->setAttribute('givenStartLine', $inlineAnnotations['given']['line']);
            $node->setAttribute('when', $inlineAnnotations['when']['value']);
            $node->setAttribute('whenStartLine', $inlineAnnotations['when']['line']);
            $node->setAttribute('then', $inlineAnnotations['then']['value']);
            $node->setAttribute('thenStartLine', $inlineAnnotations['then']['line']);
        }
        if ($this->exception !== null) {
            if ($this->exception instanceof Exception) {
                $steps = $this->exception->getSerializableTrace();
            } else {
                $steps = $this->exception->getTrace();
            }
            $class = new ReflectionClass($test);
            $file  = $class->getFileName();
            foreach ($steps as $step) {
                if (isset($step['file']) && $step['file'] === $file) {
                    $node->setAttribute('exceptionLine', $step['line']);
                    break;
                }
            }
            $node->setAttribute('exceptionMessage', $this->exception->getMessage());
        }
        $this->root->appendChild($node);
    }
}
