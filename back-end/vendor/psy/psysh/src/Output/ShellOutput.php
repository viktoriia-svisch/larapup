<?php
namespace Psy\Output;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
class ShellOutput extends ConsoleOutput
{
    const NUMBER_LINES = 128;
    private $paging = 0;
    private $pager;
    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = null, OutputFormatterInterface $formatter = null, $pager = null)
    {
        parent::__construct($verbosity, $decorated, $formatter);
        $this->initFormatters();
        if ($pager === null) {
            $this->pager = new PassthruPager($this);
        } elseif (\is_string($pager)) {
            $this->pager = new ProcOutputPager($this, $pager);
        } elseif ($pager instanceof OutputPager) {
            $this->pager = $pager;
        } else {
            throw new \InvalidArgumentException('Unexpected pager parameter: ' . $pager);
        }
    }
    public function page($messages, $type = 0)
    {
        if (\is_string($messages)) {
            $messages = (array) $messages;
        }
        if (!\is_array($messages) && !\is_callable($messages)) {
            throw new \InvalidArgumentException('Paged output requires a string, array or callback');
        }
        $this->startPaging();
        if (\is_callable($messages)) {
            $messages($this);
        } else {
            $this->write($messages, true, $type);
        }
        $this->stopPaging();
    }
    public function startPaging()
    {
        $this->paging++;
    }
    public function stopPaging()
    {
        $this->paging--;
        $this->closePager();
    }
    public function write($messages, $newline = false, $type = 0)
    {
        if ($this->getVerbosity() === self::VERBOSITY_QUIET) {
            return;
        }
        $messages = (array) $messages;
        if ($type & self::NUMBER_LINES) {
            $pad = \strlen((string) \count($messages));
            $template = $this->isDecorated() ? "<aside>%{$pad}s</aside>: %s" : "%{$pad}s: %s";
            if ($type & self::OUTPUT_RAW) {
                $messages = \array_map(['Symfony\Component\Console\Formatter\OutputFormatter', 'escape'], $messages);
            }
            foreach ($messages as $i => $line) {
                $messages[$i] = \sprintf($template, $i, $line);
            }
            $type = $type & ~self::NUMBER_LINES & ~self::OUTPUT_RAW;
        }
        parent::write($messages, $newline, $type);
    }
    public function doWrite($message, $newline)
    {
        if ($this->paging > 0) {
            $this->pager->doWrite($message, $newline);
        } else {
            parent::doWrite($message, $newline);
        }
    }
    private function closePager()
    {
        if ($this->paging <= 0) {
            $this->pager->close();
        }
    }
    private function initFormatters()
    {
        $formatter = $this->getFormatter();
        $formatter->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));
        $formatter->setStyle('error',   new OutputFormatterStyle('black', 'red', ['bold']));
        $formatter->setStyle('aside',   new OutputFormatterStyle('blue'));
        $formatter->setStyle('strong',  new OutputFormatterStyle(null, null, ['bold']));
        $formatter->setStyle('return',  new OutputFormatterStyle('cyan'));
        $formatter->setStyle('urgent',  new OutputFormatterStyle('red'));
        $formatter->setStyle('hidden',  new OutputFormatterStyle('black'));
        $formatter->setStyle('public',    new OutputFormatterStyle(null, null, ['bold']));
        $formatter->setStyle('protected', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('private',   new OutputFormatterStyle('red'));
        $formatter->setStyle('global',    new OutputFormatterStyle('cyan', null, ['bold']));
        $formatter->setStyle('const',     new OutputFormatterStyle('cyan'));
        $formatter->setStyle('class',     new OutputFormatterStyle('blue', null, ['underscore']));
        $formatter->setStyle('function',  new OutputFormatterStyle(null));
        $formatter->setStyle('default',   new OutputFormatterStyle(null));
        $formatter->setStyle('number',   new OutputFormatterStyle('magenta'));
        $formatter->setStyle('string',   new OutputFormatterStyle('green'));
        $formatter->setStyle('bool',     new OutputFormatterStyle('cyan'));
        $formatter->setStyle('keyword',  new OutputFormatterStyle('yellow'));
        $formatter->setStyle('comment',  new OutputFormatterStyle('blue'));
        $formatter->setStyle('object',   new OutputFormatterStyle('blue'));
        $formatter->setStyle('resource', new OutputFormatterStyle('yellow'));
    }
}
