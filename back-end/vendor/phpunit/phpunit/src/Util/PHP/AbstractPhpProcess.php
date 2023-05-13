<?php
namespace PHPUnit\Util\PHP;
use __PHP_Incomplete_Class;
use ErrorException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\SyntheticError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestResult;
use SebastianBergmann\Environment\Runtime;
abstract class AbstractPhpProcess
{
    protected $runtime;
    protected $stderrRedirection = false;
    protected $stdin = '';
    protected $args = '';
    protected $env = [];
    protected $timeout = 0;
    public static function factory(): self
    {
        if (\DIRECTORY_SEPARATOR === '\\') {
            return new WindowsPhpProcess;
        }
        return new DefaultPhpProcess;
    }
    public function __construct()
    {
        $this->runtime = new Runtime;
    }
    public function setUseStderrRedirection(bool $stderrRedirection): void
    {
        $this->stderrRedirection = $stderrRedirection;
    }
    public function useStderrRedirection(): bool
    {
        return $this->stderrRedirection;
    }
    public function setStdin(string $stdin): void
    {
        $this->stdin = $stdin;
    }
    public function getStdin(): string
    {
        return $this->stdin;
    }
    public function setArgs(string $args): void
    {
        $this->args = $args;
    }
    public function getArgs(): string
    {
        return $this->args;
    }
    public function setEnv(array $env): void
    {
        $this->env = $env;
    }
    public function getEnv(): array
    {
        return $this->env;
    }
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }
    public function getTimeout(): int
    {
        return $this->timeout;
    }
    public function runTestJob(string $job, Test $test, TestResult $result): void
    {
        $result->startTest($test);
        $_result = $this->runJob($job);
        $this->processChildResult(
            $test,
            $result,
            $_result['stdout'],
            $_result['stderr']
        );
    }
    public function getCommand(array $settings, string $file = null): string
    {
        $command = $this->runtime->getBinary();
        $command .= $this->settingsToParameters($settings);
        if (\PHP_SAPI === 'phpdbg') {
            $command .= ' -qrr';
            if (!$file) {
                $command .= 's=';
            }
        }
        if ($file) {
            $command .= ' ' . \escapeshellarg($file);
        }
        if ($this->args) {
            if (!$file) {
                $command .= ' --';
            }
            $command .= ' ' . $this->args;
        }
        if ($this->stderrRedirection === true) {
            $command .= ' 2>&1';
        }
        return $command;
    }
    abstract public function runJob(string $job, array $settings = []): array;
    protected function settingsToParameters(array $settings): string
    {
        $buffer = '';
        foreach ($settings as $setting) {
            $buffer .= ' -d ' . \escapeshellarg($setting);
        }
        return $buffer;
    }
    private function processChildResult(Test $test, TestResult $result, string $stdout, string $stderr): void
    {
        $time = 0;
        if (!empty($stderr)) {
            $result->addError(
                $test,
                new Exception(\trim($stderr)),
                $time
            );
        } else {
            \set_error_handler(function ($errno, $errstr, $errfile, $errline): void {
                throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
            });
            try {
                if (\strpos($stdout, "#!/usr/bin/env php\n") === 0) {
                    $stdout = \substr($stdout, 19);
                }
                $childResult = \unserialize(\str_replace("#!/usr/bin/env php\n", '', $stdout));
                \restore_error_handler();
            } catch (ErrorException $e) {
                \restore_error_handler();
                $childResult = false;
                $result->addError(
                    $test,
                    new Exception(\trim($stdout), 0, $e),
                    $time
                );
            }
            if ($childResult !== false) {
                if (!empty($childResult['output'])) {
                    $output = $childResult['output'];
                }
                $test->setResult($childResult['testResult']);
                $test->addToAssertionCount($childResult['numAssertions']);
                $childResult = $childResult['result'];
                if ($result->getCollectCodeCoverageInformation()) {
                    $result->getCodeCoverage()->merge(
                        $childResult->getCodeCoverage()
                    );
                }
                $time           = $childResult->time();
                $notImplemented = $childResult->notImplemented();
                $risky          = $childResult->risky();
                $skipped        = $childResult->skipped();
                $errors         = $childResult->errors();
                $warnings       = $childResult->warnings();
                $failures       = $childResult->failures();
                if (!empty($notImplemented)) {
                    $result->addError(
                        $test,
                        $this->getException($notImplemented[0]),
                        $time
                    );
                } elseif (!empty($risky)) {
                    $result->addError(
                        $test,
                        $this->getException($risky[0]),
                        $time
                    );
                } elseif (!empty($skipped)) {
                    $result->addError(
                        $test,
                        $this->getException($skipped[0]),
                        $time
                    );
                } elseif (!empty($errors)) {
                    $result->addError(
                        $test,
                        $this->getException($errors[0]),
                        $time
                    );
                } elseif (!empty($warnings)) {
                    $result->addWarning(
                        $test,
                        $this->getException($warnings[0]),
                        $time
                    );
                } elseif (!empty($failures)) {
                    $result->addFailure(
                        $test,
                        $this->getException($failures[0]),
                        $time
                    );
                }
            }
        }
        $result->endTest($test, $time);
        if (!empty($output)) {
            print $output;
        }
    }
    private function getException(TestFailure $error): Exception
    {
        $exception = $error->thrownException();
        if ($exception instanceof __PHP_Incomplete_Class) {
            $exceptionArray = [];
            foreach ((array) $exception as $key => $value) {
                $key                  = \substr($key, \strrpos($key, "\0") + 1);
                $exceptionArray[$key] = $value;
            }
            $exception = new SyntheticError(
                \sprintf(
                    '%s: %s',
                    $exceptionArray['_PHP_Incomplete_Class_Name'],
                    $exceptionArray['message']
                ),
                $exceptionArray['code'],
                $exceptionArray['file'],
                $exceptionArray['line'],
                $exceptionArray['trace']
            );
        }
        return $exception;
    }
}
