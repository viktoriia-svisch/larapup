<?php
namespace Psy\Command;
use Psy\Input\FilterOptions;
use Psy\Output\ShellOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class TraceCommand extends Command
{
    protected $filter;
    public function __construct($name = null)
    {
        $this->filter = new FilterOptions();
        parent::__construct($name);
    }
    protected function configure()
    {
        list($grep, $insensitive, $invert) = FilterOptions::getOptions();
        $this
            ->setName('trace')
            ->setDefinition([
                new InputOption('include-psy', 'p', InputOption::VALUE_NONE,     'Include Psy in the call stack.'),
                new InputOption('num',         'n', InputOption::VALUE_REQUIRED, 'Only include NUM lines.'),
                $grep,
                $insensitive,
                $invert,
            ])
            ->setDescription('Show the current call stack.')
            ->setHelp(
                <<<'HELP'
Show the current call stack.
Optionally, include PsySH in the call stack by passing the <info>--include-psy</info> option.
e.g.
<return>> trace -n10</return>
<return>> trace --include-psy</return>
HELP
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filter->bind($input);
        $trace = $this->getBacktrace(new \Exception(), $input->getOption('num'), $input->getOption('include-psy'));
        $output->page($trace, ShellOutput::NUMBER_LINES);
    }
    protected function getBacktrace(\Exception $e, $count = null, $includePsy = true)
    {
        if ($cwd = \getcwd()) {
            $cwd = \rtrim($cwd, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
        if ($count === null) {
            $count = PHP_INT_MAX;
        }
        $lines = [];
        $trace = $e->getTrace();
        \array_unshift($trace, [
            'function' => '',
            'file'     => $e->getFile() !== null ? $e->getFile() : 'n/a',
            'line'     => $e->getLine() !== null ? $e->getLine() : 'n/a',
            'args'     => [],
        ]);
        if (!$includePsy) {
            for ($i = \count($trace) - 1; $i >= 0; $i--) {
                $thing = isset($trace[$i]['class']) ? $trace[$i]['class'] : $trace[$i]['function'];
                if (\preg_match('/\\\\?Psy\\\\/', $thing)) {
                    $trace = \array_slice($trace, $i + 1);
                    break;
                }
            }
        }
        for ($i = 0, $count = \min($count, \count($trace)); $i < $count; $i++) {
            $class    = isset($trace[$i]['class']) ? $trace[$i]['class'] : '';
            $type     = isset($trace[$i]['type']) ? $trace[$i]['type'] : '';
            $function = $trace[$i]['function'];
            $file     = isset($trace[$i]['file']) ? $this->replaceCwd($cwd, $trace[$i]['file']) : 'n/a';
            $line     = isset($trace[$i]['line']) ? $trace[$i]['line'] : 'n/a';
            if (\preg_match("#/src/Execution(?:Loop)?Closure.php\(\d+\) : eval\(\)'d code$#", \str_replace('\\', '/', $file))) {
                $file = "eval()'d code";
            }
            if (!$this->filter->match(\sprintf('%s%s%s() at %s:%s', $class, $type, $function, $file, $line))) {
                continue;
            }
            $lines[] = \sprintf(
                ' <class>%s</class>%s%s() at <info>%s:%s</info>',
                OutputFormatter::escape($class),
                OutputFormatter::escape($type),
                OutputFormatter::escape($function),
                OutputFormatter::escape($file),
                OutputFormatter::escape($line)
            );
        }
        return $lines;
    }
    private function replaceCwd($cwd, $file)
    {
        if ($cwd === false) {
            return $file;
        } else {
            return \preg_replace('/^' . \preg_quote($cwd, '/') . '/', '', $file);
        }
    }
}
