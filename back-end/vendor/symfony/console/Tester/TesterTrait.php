<?php
namespace Symfony\Component\Console\Tester;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
trait TesterTrait
{
    private $output;
    private $inputs = [];
    private $captureStreamsIndependently = false;
    public function getDisplay($normalize = false)
    {
        rewind($this->output->getStream());
        $display = stream_get_contents($this->output->getStream());
        if ($normalize) {
            $display = str_replace(PHP_EOL, "\n", $display);
        }
        return $display;
    }
    public function getErrorOutput($normalize = false)
    {
        if (!$this->captureStreamsIndependently) {
            throw new \LogicException('The error output is not available when the tester is run without "capture_stderr_separately" option set.');
        }
        rewind($this->output->getErrorOutput()->getStream());
        $display = stream_get_contents($this->output->getErrorOutput()->getStream());
        if ($normalize) {
            $display = str_replace(PHP_EOL, "\n", $display);
        }
        return $display;
    }
    public function getInput()
    {
        return $this->input;
    }
    public function getOutput()
    {
        return $this->output;
    }
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    public function setInputs(array $inputs)
    {
        $this->inputs = $inputs;
        return $this;
    }
    private function initOutput(array $options)
    {
        $this->captureStreamsIndependently = \array_key_exists('capture_stderr_separately', $options) && $options['capture_stderr_separately'];
        if (!$this->captureStreamsIndependently) {
            $this->output = new StreamOutput(fopen('php:
            if (isset($options['decorated'])) {
                $this->output->setDecorated($options['decorated']);
            }
            if (isset($options['verbosity'])) {
                $this->output->setVerbosity($options['verbosity']);
            }
        } else {
            $this->output = new ConsoleOutput(
                isset($options['verbosity']) ? $options['verbosity'] : ConsoleOutput::VERBOSITY_NORMAL,
                isset($options['decorated']) ? $options['decorated'] : null
            );
            $errorOutput = new StreamOutput(fopen('php:
            $errorOutput->setFormatter($this->output->getFormatter());
            $errorOutput->setVerbosity($this->output->getVerbosity());
            $errorOutput->setDecorated($this->output->isDecorated());
            $reflectedOutput = new \ReflectionObject($this->output);
            $strErrProperty = $reflectedOutput->getProperty('stderr');
            $strErrProperty->setAccessible(true);
            $strErrProperty->setValue($this->output, $errorOutput);
            $reflectedParent = $reflectedOutput->getParentClass();
            $streamProperty = $reflectedParent->getProperty('stream');
            $streamProperty->setAccessible(true);
            $streamProperty->setValue($this->output, fopen('php:
        }
    }
    private static function createStream(array $inputs)
    {
        $stream = fopen('php:
        foreach ($inputs as $input) {
            fwrite($stream, $input.PHP_EOL);
        }
        rewind($stream);
        return $stream;
    }
}
