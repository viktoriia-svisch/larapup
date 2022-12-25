<?php
namespace PHPUnit\Framework;
use AssertionError;
use Countable;
use Error;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Util\Blacklist;
use PHPUnit\Util\ErrorHandler;
use PHPUnit\Util\Printer;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\CoveredCodeNotExecutedException as OriginalCoveredCodeNotExecutedException;
use SebastianBergmann\CodeCoverage\Exception as OriginalCodeCoverageException;
use SebastianBergmann\CodeCoverage\MissingCoversAnnotationException as OriginalMissingCoversAnnotationException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\Invoker\TimeoutException;
use SebastianBergmann\ResourceOperations\ResourceOperations;
use SebastianBergmann\Timer\Timer;
use Throwable;
class TestResult implements Countable
{
    protected $passed = [];
    protected $errors = [];
    protected $failures = [];
    protected $warnings = [];
    protected $notImplemented = [];
    protected $risky = [];
    protected $skipped = [];
    protected $listeners = [];
    protected $runTests = 0;
    protected $time = 0;
    protected $topTestSuite;
    protected $codeCoverage;
    protected $convertErrorsToExceptions = true;
    protected $stop = false;
    protected $stopOnError = false;
    protected $stopOnFailure = false;
    protected $stopOnWarning = false;
    protected $beStrictAboutTestsThatDoNotTestAnything = true;
    protected $beStrictAboutOutputDuringTests = false;
    protected $beStrictAboutTodoAnnotatedTests = false;
    protected $beStrictAboutResourceUsageDuringSmallTests = false;
    protected $enforceTimeLimit = false;
    protected $timeoutForSmallTests = 1;
    protected $timeoutForMediumTests = 10;
    protected $timeoutForLargeTests = 60;
    protected $stopOnRisky = false;
    protected $stopOnIncomplete = false;
    protected $stopOnSkipped = false;
    protected $lastTestFailed = false;
    private $defaultTimeLimit = 0;
    private $stopOnDefect = false;
    private $registerMockObjectsFromTestArgumentsRecursively = false;
    public static function isAnyCoverageRequired(TestCase $test)
    {
        $annotations = $test->getAnnotations();
        if (isset($annotations['method']['covers'])) {
            return true;
        }
        if (isset($annotations['class']['coversNothing'])) {
            return false;
        }
        return true;
    }
    public function addListener(TestListener $listener): void
    {
        $this->listeners[] = $listener;
    }
    public function removeListener(TestListener $listener): void
    {
        foreach ($this->listeners as $key => $_listener) {
            if ($listener === $_listener) {
                unset($this->listeners[$key]);
            }
        }
    }
    public function flushListeners(): void
    {
        foreach ($this->listeners as $listener) {
            if ($listener instanceof Printer) {
                $listener->flush();
            }
        }
    }
    public function addError(Test $test, Throwable $t, float $time): void
    {
        if ($t instanceof RiskyTest) {
            $this->risky[] = new TestFailure($test, $t);
            $notifyMethod  = 'addRiskyTest';
            if ($test instanceof TestCase) {
                $test->markAsRisky();
            }
            if ($this->stopOnRisky || $this->stopOnDefect) {
                $this->stop();
            }
        } elseif ($t instanceof IncompleteTest) {
            $this->notImplemented[] = new TestFailure($test, $t);
            $notifyMethod           = 'addIncompleteTest';
            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        } elseif ($t instanceof SkippedTest) {
            $this->skipped[] = new TestFailure($test, $t);
            $notifyMethod    = 'addSkippedTest';
            if ($this->stopOnSkipped) {
                $this->stop();
            }
        } else {
            $this->errors[] = new TestFailure($test, $t);
            $notifyMethod   = 'addError';
            if ($this->stopOnError || $this->stopOnFailure) {
                $this->stop();
            }
        }
        if ($t instanceof Error) {
            $t = new ExceptionWrapper($t);
        }
        foreach ($this->listeners as $listener) {
            $listener->$notifyMethod($test, $t, $time);
        }
        $this->lastTestFailed = true;
        $this->time += $time;
    }
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        if ($this->stopOnWarning || $this->stopOnDefect) {
            $this->stop();
        }
        $this->warnings[] = new TestFailure($test, $e);
        foreach ($this->listeners as $listener) {
            $listener->addWarning($test, $e, $time);
        }
        $this->time += $time;
    }
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        if ($e instanceof RiskyTest || $e instanceof OutputError) {
            $this->risky[] = new TestFailure($test, $e);
            $notifyMethod  = 'addRiskyTest';
            if ($test instanceof TestCase) {
                $test->markAsRisky();
            }
            if ($this->stopOnRisky || $this->stopOnDefect) {
                $this->stop();
            }
        } elseif ($e instanceof IncompleteTest) {
            $this->notImplemented[] = new TestFailure($test, $e);
            $notifyMethod           = 'addIncompleteTest';
            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        } elseif ($e instanceof SkippedTest) {
            $this->skipped[] = new TestFailure($test, $e);
            $notifyMethod    = 'addSkippedTest';
            if ($this->stopOnSkipped) {
                $this->stop();
            }
        } else {
            $this->failures[] = new TestFailure($test, $e);
            $notifyMethod     = 'addFailure';
            if ($this->stopOnFailure || $this->stopOnDefect) {
                $this->stop();
            }
        }
        foreach ($this->listeners as $listener) {
            $listener->$notifyMethod($test, $e, $time);
        }
        $this->lastTestFailed = true;
        $this->time += $time;
    }
    public function startTestSuite(TestSuite $suite): void
    {
        if ($this->topTestSuite === null) {
            $this->topTestSuite = $suite;
        }
        foreach ($this->listeners as $listener) {
            $listener->startTestSuite($suite);
        }
    }
    public function endTestSuite(TestSuite $suite): void
    {
        foreach ($this->listeners as $listener) {
            $listener->endTestSuite($suite);
        }
    }
    public function startTest(Test $test): void
    {
        $this->lastTestFailed = false;
        $this->runTests += \count($test);
        foreach ($this->listeners as $listener) {
            $listener->startTest($test);
        }
    }
    public function endTest(Test $test, float $time): void
    {
        foreach ($this->listeners as $listener) {
            $listener->endTest($test, $time);
        }
        if (!$this->lastTestFailed && $test instanceof TestCase) {
            $class = \get_class($test);
            $key   = $class . '::' . $test->getName();
            $this->passed[$key] = [
                'result' => $test->getResult(),
                'size'   => \PHPUnit\Util\Test::getSize(
                    $class,
                    $test->getName(false)
                ),
            ];
            $this->time += $time;
        }
    }
    public function allHarmless(): bool
    {
        return $this->riskyCount() == 0;
    }
    public function riskyCount(): int
    {
        return \count($this->risky);
    }
    public function allCompletelyImplemented(): bool
    {
        return $this->notImplementedCount() == 0;
    }
    public function notImplementedCount(): int
    {
        return \count($this->notImplemented);
    }
    public function risky(): array
    {
        return $this->risky;
    }
    public function notImplemented(): array
    {
        return $this->notImplemented;
    }
    public function noneSkipped(): bool
    {
        return $this->skippedCount() == 0;
    }
    public function skippedCount(): int
    {
        return \count($this->skipped);
    }
    public function skipped(): array
    {
        return $this->skipped;
    }
    public function errorCount(): int
    {
        return \count($this->errors);
    }
    public function errors(): array
    {
        return $this->errors;
    }
    public function failureCount(): int
    {
        return \count($this->failures);
    }
    public function failures(): array
    {
        return $this->failures;
    }
    public function warningCount(): int
    {
        return \count($this->warnings);
    }
    public function warnings(): array
    {
        return $this->warnings;
    }
    public function passed(): array
    {
        return $this->passed;
    }
    public function topTestSuite(): TestSuite
    {
        return $this->topTestSuite;
    }
    public function getCollectCodeCoverageInformation(): bool
    {
        return $this->codeCoverage !== null;
    }
    public function run(Test $test): void
    {
        Assert::resetCount();
        $coversNothing = false;
        if ($test instanceof TestCase) {
            $test->setRegisterMockObjectsFromTestArgumentsRecursively(
                $this->registerMockObjectsFromTestArgumentsRecursively
            );
            $isAnyCoverageRequired = self::isAnyCoverageRequired($test);
        }
        $error      = false;
        $failure    = false;
        $warning    = false;
        $incomplete = false;
        $risky      = false;
        $skipped    = false;
        $this->startTest($test);
        $errorHandlerSet = false;
        if ($this->convertErrorsToExceptions) {
            $oldErrorHandler = \set_error_handler(
                [ErrorHandler::class, 'handleError'],
                \E_ALL | \E_STRICT
            );
            if ($oldErrorHandler === null) {
                $errorHandlerSet = true;
            } else {
                \restore_error_handler();
            }
        }
        $collectCodeCoverage = $this->codeCoverage !== null &&
                               !$test instanceof WarningTestCase &&
                               $isAnyCoverageRequired;
        if ($collectCodeCoverage) {
            $this->codeCoverage->start($test);
        }
        $monitorFunctions = $this->beStrictAboutResourceUsageDuringSmallTests &&
            !$test instanceof WarningTestCase &&
            $test->getSize() == \PHPUnit\Util\Test::SMALL &&
            \function_exists('xdebug_start_function_monitor');
        if ($monitorFunctions) {
            \xdebug_start_function_monitor(ResourceOperations::getFunctions());
        }
        Timer::start();
        try {
            if (!$test instanceof WarningTestCase &&
                $this->enforceTimeLimit &&
                ($this->defaultTimeLimit || $test->getSize() != \PHPUnit\Util\Test::UNKNOWN) &&
                \extension_loaded('pcntl') && \class_exists(Invoker::class)) {
                switch ($test->getSize()) {
                    case \PHPUnit\Util\Test::SMALL:
                        $_timeout = $this->timeoutForSmallTests;
                        break;
                    case \PHPUnit\Util\Test::MEDIUM:
                        $_timeout = $this->timeoutForMediumTests;
                        break;
                    case \PHPUnit\Util\Test::LARGE:
                        $_timeout = $this->timeoutForLargeTests;
                        break;
                    case \PHPUnit\Util\Test::UNKNOWN:
                        $_timeout = $this->defaultTimeLimit;
                        break;
                }
                $invoker = new Invoker;
                $invoker->invoke([$test, 'runBare'], [], $_timeout);
            } else {
                $test->runBare();
            }
        } catch (TimeoutException $e) {
            $this->addFailure(
                $test,
                new RiskyTestError(
                    $e->getMessage()
                ),
                $_timeout
            );
            $risky = true;
        } catch (MockObjectException $e) {
            $e = new Warning(
                $e->getMessage()
            );
            $warning = true;
        } catch (AssertionFailedError $e) {
            $failure = true;
            if ($e instanceof RiskyTestError) {
                $risky = true;
            } elseif ($e instanceof IncompleteTestError) {
                $incomplete = true;
            } elseif ($e instanceof SkippedTestError) {
                $skipped = true;
            }
        } catch (AssertionError $e) {
            $test->addToAssertionCount(1);
            $failure = true;
            $frame   = $e->getTrace()[0];
            $e = new AssertionFailedError(
                \sprintf(
                    '%s in %s:%s',
                    $e->getMessage(),
                    $frame['file'],
                    $frame['line']
                )
            );
        } catch (Warning $e) {
            $warning = true;
        } catch (Exception $e) {
            $error = true;
        } catch (Throwable $e) {
            $e     = new ExceptionWrapper($e);
            $error = true;
        }
        $time = Timer::stop();
        $test->addToAssertionCount(Assert::getCount());
        if ($monitorFunctions) {
            $blacklist = new Blacklist;
            $functions = \xdebug_get_monitored_functions();
            \xdebug_stop_function_monitor();
            foreach ($functions as $function) {
                if (!$blacklist->isBlacklisted($function['filename'])) {
                    $this->addFailure(
                        $test,
                        new RiskyTestError(
                            \sprintf(
                                '%s() used in %s:%s',
                                $function['function'],
                                $function['filename'],
                                $function['lineno']
                            )
                        ),
                        $time
                    );
                }
            }
        }
        if ($this->beStrictAboutTestsThatDoNotTestAnything &&
            $test->getNumAssertions() == 0) {
            $risky = true;
        }
        if ($collectCodeCoverage) {
            $append           = !$risky && !$incomplete && !$skipped;
            $linesToBeCovered = [];
            $linesToBeUsed    = [];
            if ($append && $test instanceof TestCase) {
                try {
                    $linesToBeCovered = \PHPUnit\Util\Test::getLinesToBeCovered(
                        \get_class($test),
                        $test->getName(false)
                    );
                    $linesToBeUsed = \PHPUnit\Util\Test::getLinesToBeUsed(
                        \get_class($test),
                        $test->getName(false)
                    );
                } catch (InvalidCoversTargetException $cce) {
                    $this->addWarning(
                        $test,
                        new Warning(
                            $cce->getMessage()
                        ),
                        $time
                    );
                }
            }
            try {
                $this->codeCoverage->stop(
                    $append,
                    $linesToBeCovered,
                    $linesToBeUsed
                );
            } catch (UnintentionallyCoveredCodeException $cce) {
                $this->addFailure(
                    $test,
                    new UnintentionallyCoveredCodeError(
                        'This test executed code that is not listed as code to be covered or used:' .
                        \PHP_EOL . $cce->getMessage()
                    ),
                    $time
                );
            } catch (OriginalCoveredCodeNotExecutedException $cce) {
                $this->addFailure(
                    $test,
                    new CoveredCodeNotExecutedException(
                        'This test did not execute all the code that is listed as code to be covered:' .
                        \PHP_EOL . $cce->getMessage()
                    ),
                    $time
                );
            } catch (OriginalMissingCoversAnnotationException $cce) {
                if ($linesToBeCovered !== false) {
                    $this->addFailure(
                        $test,
                        new MissingCoversAnnotationException(
                            'This test does not have a @covers annotation but is expected to have one'
                        ),
                        $time
                    );
                }
            } catch (OriginalCodeCoverageException $cce) {
                $error = true;
                $e = $e ?? $cce;
            }
        }
        if ($errorHandlerSet === true) {
            \restore_error_handler();
        }
        if ($error === true) {
            $this->addError($test, $e, $time);
        } elseif ($failure === true) {
            $this->addFailure($test, $e, $time);
        } elseif ($warning === true) {
            $this->addWarning($test, $e, $time);
        } elseif ($this->beStrictAboutTestsThatDoNotTestAnything &&
            !$test->doesNotPerformAssertions() &&
            $test->getNumAssertions() == 0) {
            $reflected = new \ReflectionClass($test);
            $name      = $test->getName(false);
            if ($name && $reflected->hasMethod($name)) {
                $reflected = $reflected->getMethod($name);
            }
            $this->addFailure(
                $test,
                new RiskyTestError(\sprintf(
                    "This test did not perform any assertions\n\n%s:%d",
                    $reflected->getFileName(),
                    $reflected->getStartLine()
                )),
                $time
            );
        } elseif ($this->beStrictAboutTestsThatDoNotTestAnything &&
            $test->doesNotPerformAssertions() &&
            $test->getNumAssertions() > 0) {
            $this->addFailure(
                $test,
                new RiskyTestError(\sprintf(
                    'This test is annotated with "@doesNotPerformAssertions" but performed %d assertions',
                    $test->getNumAssertions()
                )),
                $time
            );
        } elseif ($this->beStrictAboutOutputDuringTests && $test->hasOutput()) {
            $this->addFailure(
                $test,
                new OutputError(
                    \sprintf(
                        'This test printed output: %s',
                        $test->getActualOutput()
                    )
                ),
                $time
            );
        } elseif ($this->beStrictAboutTodoAnnotatedTests && $test instanceof TestCase) {
            $annotations = $test->getAnnotations();
            if (isset($annotations['method']['todo'])) {
                $this->addFailure(
                    $test,
                    new RiskyTestError(
                        'Test method is annotated with @todo'
                    ),
                    $time
                );
            }
        }
        $this->endTest($test, $time);
    }
    public function count(): int
    {
        return $this->runTests;
    }
    public function shouldStop(): bool
    {
        return $this->stop;
    }
    public function stop(): void
    {
        $this->stop = true;
    }
    public function getCodeCoverage(): ?CodeCoverage
    {
        return $this->codeCoverage;
    }
    public function setCodeCoverage(CodeCoverage $codeCoverage): void
    {
        $this->codeCoverage = $codeCoverage;
    }
    public function convertErrorsToExceptions(bool $flag): void
    {
        $this->convertErrorsToExceptions = $flag;
    }
    public function getConvertErrorsToExceptions(): bool
    {
        return $this->convertErrorsToExceptions;
    }
    public function stopOnError(bool $flag): void
    {
        $this->stopOnError = $flag;
    }
    public function stopOnFailure(bool $flag): void
    {
        $this->stopOnFailure = $flag;
    }
    public function stopOnWarning(bool $flag): void
    {
        $this->stopOnWarning = $flag;
    }
    public function beStrictAboutTestsThatDoNotTestAnything(bool $flag): void
    {
        $this->beStrictAboutTestsThatDoNotTestAnything = $flag;
    }
    public function isStrictAboutTestsThatDoNotTestAnything(): bool
    {
        return $this->beStrictAboutTestsThatDoNotTestAnything;
    }
    public function beStrictAboutOutputDuringTests(bool $flag): void
    {
        $this->beStrictAboutOutputDuringTests = $flag;
    }
    public function isStrictAboutOutputDuringTests(): bool
    {
        return $this->beStrictAboutOutputDuringTests;
    }
    public function beStrictAboutResourceUsageDuringSmallTests(bool $flag): void
    {
        $this->beStrictAboutResourceUsageDuringSmallTests = $flag;
    }
    public function isStrictAboutResourceUsageDuringSmallTests(): bool
    {
        return $this->beStrictAboutResourceUsageDuringSmallTests;
    }
    public function enforceTimeLimit(bool $flag): void
    {
        $this->enforceTimeLimit = $flag;
    }
    public function enforcesTimeLimit(): bool
    {
        return $this->enforceTimeLimit;
    }
    public function beStrictAboutTodoAnnotatedTests(bool $flag): void
    {
        $this->beStrictAboutTodoAnnotatedTests = $flag;
    }
    public function isStrictAboutTodoAnnotatedTests(): bool
    {
        return $this->beStrictAboutTodoAnnotatedTests;
    }
    public function stopOnRisky(bool $flag): void
    {
        $this->stopOnRisky = $flag;
    }
    public function stopOnIncomplete(bool $flag): void
    {
        $this->stopOnIncomplete = $flag;
    }
    public function stopOnSkipped(bool $flag): void
    {
        $this->stopOnSkipped = $flag;
    }
    public function stopOnDefect(bool $flag): void
    {
        $this->stopOnDefect = $flag;
    }
    public function time(): float
    {
        return $this->time;
    }
    public function wasSuccessful(): bool
    {
        return $this->wasSuccessfulIgnoringWarnings() && empty($this->warnings);
    }
    public function wasSuccessfulIgnoringWarnings(): bool
    {
        return empty($this->errors) && empty($this->failures);
    }
    public function setDefaultTimeLimit(int $timeout): void
    {
        $this->defaultTimeLimit = $timeout;
    }
    public function setTimeoutForSmallTests(int $timeout): void
    {
        $this->timeoutForSmallTests = $timeout;
    }
    public function setTimeoutForMediumTests(int $timeout): void
    {
        $this->timeoutForMediumTests = $timeout;
    }
    public function setTimeoutForLargeTests(int $timeout): void
    {
        $this->timeoutForLargeTests = $timeout;
    }
    public function getTimeoutForLargeTests(): int
    {
        return $this->timeoutForLargeTests;
    }
    public function setRegisterMockObjectsFromTestArgumentsRecursively(bool $flag): void
    {
        $this->registerMockObjectsFromTestArgumentsRecursively = $flag;
    }
}
