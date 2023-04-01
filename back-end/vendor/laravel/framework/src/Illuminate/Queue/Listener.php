<?php
namespace Illuminate\Queue;
use Closure;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
class Listener
{
    protected $commandPath;
    protected $environment;
    protected $sleep = 3;
    protected $maxTries = 0;
    protected $outputHandler;
    public function __construct($commandPath)
    {
        $this->commandPath = $commandPath;
    }
    protected function phpBinary()
    {
        return (new PhpExecutableFinder)->find(false);
    }
    protected function artisanBinary()
    {
        return defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan';
    }
    public function listen($connection, $queue, ListenerOptions $options)
    {
        $process = $this->makeProcess($connection, $queue, $options);
        while (true) {
            $this->runProcess($process, $options->memory);
        }
    }
    public function makeProcess($connection, $queue, ListenerOptions $options)
    {
        $command = $this->createCommand(
            $connection,
            $queue,
            $options
        );
        if (isset($options->environment)) {
            $command = $this->addEnvironment($command, $options);
        }
        return new Process(
            $command,
            $this->commandPath,
            null,
            null,
            $options->timeout
        );
    }
    protected function addEnvironment($command, ListenerOptions $options)
    {
        return array_merge($command, ["--env={$options->environment}"]);
    }
    protected function createCommand($connection, $queue, ListenerOptions $options)
    {
        return array_filter([
            $this->phpBinary(),
            $this->artisanBinary(),
            'queue:work',
            $connection,
            '--once',
            "--queue={$queue}",
            "--delay={$options->delay}",
            "--memory={$options->memory}",
            "--sleep={$options->sleep}",
            "--tries={$options->maxTries}",
        ], function ($value) {
            return ! is_null($value);
        });
    }
    public function runProcess(Process $process, $memory)
    {
        $process->run(function ($type, $line) {
            $this->handleWorkerOutput($type, $line);
        });
        if ($this->memoryExceeded($memory)) {
            $this->stop();
        }
    }
    protected function handleWorkerOutput($type, $line)
    {
        if (isset($this->outputHandler)) {
            call_user_func($this->outputHandler, $type, $line);
        }
    }
    public function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage(true) / 1024 / 1024) >= $memoryLimit;
    }
    public function stop()
    {
        die;
    }
    public function setOutputHandler(Closure $outputHandler)
    {
        $this->outputHandler = $outputHandler;
    }
}
