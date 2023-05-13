<?php
namespace Symfony\Component\Console\Output;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
class ConsoleOutput extends StreamOutput implements ConsoleOutputInterface
{
    private $stderr;
    private $consoleSectionOutputs = [];
    public function __construct(int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = null, OutputFormatterInterface $formatter = null)
    {
        parent::__construct($this->openOutputStream(), $verbosity, $decorated, $formatter);
        $actualDecorated = $this->isDecorated();
        $this->stderr = new StreamOutput($this->openErrorStream(), $verbosity, $decorated, $this->getFormatter());
        if (null === $decorated) {
            $this->setDecorated($actualDecorated && $this->stderr->isDecorated());
        }
    }
    public function section(): ConsoleSectionOutput
    {
        return new ConsoleSectionOutput($this->getStream(), $this->consoleSectionOutputs, $this->getVerbosity(), $this->isDecorated(), $this->getFormatter());
    }
    public function setDecorated($decorated)
    {
        parent::setDecorated($decorated);
        $this->stderr->setDecorated($decorated);
    }
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        parent::setFormatter($formatter);
        $this->stderr->setFormatter($formatter);
    }
    public function setVerbosity($level)
    {
        parent::setVerbosity($level);
        $this->stderr->setVerbosity($level);
    }
    public function getErrorOutput()
    {
        return $this->stderr;
    }
    public function setErrorOutput(OutputInterface $error)
    {
        $this->stderr = $error;
    }
    protected function hasStdoutSupport()
    {
        return false === $this->isRunningOS400();
    }
    protected function hasStderrSupport()
    {
        return false === $this->isRunningOS400();
    }
    private function isRunningOS400()
    {
        $checks = [
            \function_exists('php_uname') ? php_uname('s') : '',
            getenv('OSTYPE'),
            PHP_OS,
        ];
        return false !== stripos(implode(';', $checks), 'OS400');
    }
    private function openOutputStream()
    {
        if (!$this->hasStdoutSupport()) {
            return fopen('php:
        }
        return @fopen('php:
    }
    private function openErrorStream()
    {
        return fopen($this->hasStderrSupport() ? 'php:
    }
}
