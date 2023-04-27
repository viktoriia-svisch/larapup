<?php
namespace Illuminate\Foundation\Testing\Concerns;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\PendingCommand;
trait InteractsWithConsole
{
    public $mockConsoleOutput = true;
    public $expectedOutput = [];
    public $expectedQuestions = [];
    public function artisan($command, $parameters = [])
    {
        if (! $this->mockConsoleOutput) {
            return $this->app[Kernel::class]->call($command, $parameters);
        }
        $this->beforeApplicationDestroyed(function () {
            if (count($this->expectedQuestions)) {
                $this->fail('Question "'.array_first($this->expectedQuestions)[0].'" was not asked.');
            }
            if (count($this->expectedOutput)) {
                $this->fail('Output "'.array_first($this->expectedOutput).'" was not printed.');
            }
        });
        return new PendingCommand($this, $this->app, $command, $parameters);
    }
    protected function withoutMockingConsoleOutput()
    {
        $this->mockConsoleOutput = false;
        $this->app->offsetUnset(OutputStyle::class);
        return $this;
    }
}
