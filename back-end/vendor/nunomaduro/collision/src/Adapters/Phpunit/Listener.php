<?php
namespace NunoMaduro\Collision\Adapters\Phpunit;
use ReflectionObject;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\Warning;
use Whoops\Exception\Inspector;
use NunoMaduro\Collision\Writer;
use PHPUnit\Framework\TestSuite;
use Symfony\Component\Console\Application;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use NunoMaduro\Collision\Contracts\Writer as WriterContract;
use NunoMaduro\Collision\Contracts\Adapters\Phpunit\Listener as ListenerContract;
if (class_exists(\PHPUnit\Runner\Version::class) && substr(\PHPUnit\Runner\Version::id(), 0, 2) === '7.') {
    class Listener implements ListenerContract
    {
        protected $writer;
        protected $exceptionFound;
        public function __construct(WriterContract $writer = null)
        {
            $this->writer = $writer ?: $this->buildWriter();
        }
        public function render(\Throwable $t)
        {
            $inspector = new Inspector($t);
            $this->writer->write($inspector);
        }
        public function addError(Test $test, \Throwable $t, float $time): void
        {
            if ($this->exceptionFound === null) {
                $this->exceptionFound = $t;
            }
        }
        public function addWarning(Test $test, Warning $t, float $time): void
        {
        }
        public function addFailure(Test $test, AssertionFailedError $t, float $time): void
        {
            $this->writer->ignoreFilesIn(['/vendor/'])
            ->showTrace(false);
            if ($this->exceptionFound === null) {
                $this->exceptionFound = $t;
            }
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
        }
        public function endTest(Test $test, float $time): void
        {
        }
        public function __destruct()
        {
            if ($this->exceptionFound !== null) {
                $this->render($this->exceptionFound);
            }
        }
        protected function buildWriter(): WriterContract
        {
            $writer = new Writer;
            $application = new Application();
            $reflector = new ReflectionObject($application);
            $method = $reflector->getMethod('configureIO');
            $method->setAccessible(true);
            $method->invoke($application, new ArgvInput, $output = new ConsoleOutput);
            return $writer->setOutput($output);
        }
    }
}
