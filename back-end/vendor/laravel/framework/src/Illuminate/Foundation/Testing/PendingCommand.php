<?php
namespace Illuminate\Foundation\Testing;
use Mockery;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArrayInput;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Mockery\Exception\NoMatchingExpectationException;
class PendingCommand
{
    public $test;
    protected $app;
    protected $command;
    protected $parameters;
    protected $expectedExitCode;
    protected $hasExecuted = false;
    public function __construct(PHPUnitTestCase $test, $app, $command, $parameters)
    {
        $this->app = $app;
        $this->test = $test;
        $this->command = $command;
        $this->parameters = $parameters;
    }
    public function expectsQuestion($question, $answer)
    {
        $this->test->expectedQuestions[] = [$question, $answer];
        return $this;
    }
    public function expectsOutput($output)
    {
        $this->test->expectedOutput[] = $output;
        return $this;
    }
    public function assertExitCode($exitCode)
    {
        $this->expectedExitCode = $exitCode;
        return $this;
    }
    public function execute()
    {
        return $this->run();
    }
    public function run()
    {
        $this->hasExecuted = true;
        $this->mockConsoleOutput();
        try {
            $exitCode = $this->app[Kernel::class]->call($this->command, $this->parameters);
        } catch (NoMatchingExpectationException $e) {
            if ($e->getMethodName() === 'askQuestion') {
                $this->test->fail('Unexpected question "'.$e->getActualArguments()[0]->getQuestion().'" was asked.');
            }
            throw $e;
        }
        if ($this->expectedExitCode !== null) {
            $this->test->assertEquals(
                $this->expectedExitCode, $exitCode,
                "Expected status code {$this->expectedExitCode} but received {$exitCode}."
            );
        }
        return $exitCode;
    }
    protected function mockConsoleOutput()
    {
        $mock = Mockery::mock(OutputStyle::class.'[askQuestion]', [
            (new ArrayInput($this->parameters)), $this->createABufferedOutputMock(),
        ]);
        foreach ($this->test->expectedQuestions as $i => $question) {
            $mock->shouldReceive('askQuestion')
                ->once()
                ->ordered()
                ->with(Mockery::on(function ($argument) use ($question) {
                    return $argument->getQuestion() == $question[0];
                }))
                ->andReturnUsing(function () use ($question, $i) {
                    unset($this->test->expectedQuestions[$i]);
                    return $question[1];
                });
        }
        $this->app->bind(OutputStyle::class, function () use ($mock) {
            return $mock;
        });
    }
    private function createABufferedOutputMock()
    {
        $mock = Mockery::mock(BufferedOutput::class.'[doWrite]')
                ->shouldAllowMockingProtectedMethods()
                ->shouldIgnoreMissing();
        foreach ($this->test->expectedOutput as $i => $output) {
            $mock->shouldReceive('doWrite')
                ->once()
                ->ordered()
                ->with($output, Mockery::any())
                ->andReturnUsing(function () use ($i) {
                    unset($this->test->expectedOutput[$i]);
                });
        }
        return $mock;
    }
    public function __destruct()
    {
        if ($this->hasExecuted) {
            return;
        }
        $this->run();
    }
}
