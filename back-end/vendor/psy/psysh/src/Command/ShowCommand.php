<?php
namespace Psy\Command;
use JakubOnderka\PhpConsoleHighlighter\Highlighter;
use Psy\Configuration;
use Psy\ConsoleColorFactory;
use Psy\Exception\RuntimeException;
use Psy\Formatter\CodeFormatter;
use Psy\Formatter\SignatureFormatter;
use Psy\Input\CodeArgument;
use Psy\Output\ShellOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class ShowCommand extends ReflectingCommand
{
    private $colorMode;
    private $highlighter;
    private $lastException;
    private $lastExceptionIndex;
    public function __construct($colorMode = null)
    {
        $this->colorMode = $colorMode ?: Configuration::COLOR_MODE_AUTO;
        parent::__construct();
    }
    protected function configure()
    {
        $this
            ->setName('show')
            ->setDefinition([
                new CodeArgument('target', CodeArgument::OPTIONAL, 'Function, class, instance, constant, method or property to show.'),
                new InputOption('ex', null,  InputOption::VALUE_OPTIONAL, 'Show last exception context. Optionally specify a stack index.', 1),
            ])
            ->setDescription('Show the code for an object, class, constant, method or property.')
            ->setHelp(
                <<<HELP
Show the code for an object, class, constant, method or property, or the context
of the last exception.
<return>cat --ex</return> defaults to showing the lines surrounding the location of the last
exception. Invoking it more than once travels up the exception's stack trace,
and providing a number shows the context of the given index of the trace.
e.g.
<return>>>> show \$myObject</return>
<return>>>> show Psy\Shell::debug</return>
<return>>>> show --ex</return>
<return>>>> show --ex 3</return>
HELP
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $opts = $input->getOptions();
        if ($opts['ex'] !== 1) {
            if ($input->getArgument('target')) {
                throw new \InvalidArgumentException('Too many arguments (supply either "target" or "--ex")');
            }
            return $this->writeExceptionContext($input, $output);
        }
        if ($input->getArgument('target')) {
            return $this->writeCodeContext($input, $output);
        }
        throw new RuntimeException('Not enough arguments (missing: "target")');
    }
    private function writeCodeContext(InputInterface $input, OutputInterface $output)
    {
        list($target, $reflector) = $this->getTargetAndReflector($input->getArgument('target'));
        $this->setCommandScopeVariables($reflector);
        try {
            $output->page(CodeFormatter::format($reflector, $this->colorMode), ShellOutput::OUTPUT_RAW);
        } catch (RuntimeException $e) {
            $output->writeln(SignatureFormatter::format($reflector));
            throw $e;
        }
    }
    private function writeExceptionContext(InputInterface $input, OutputInterface $output)
    {
        $exception = $this->context->getLastException();
        if ($exception !== $this->lastException) {
            $this->lastException = null;
            $this->lastExceptionIndex = null;
        }
        $opts = $input->getOptions();
        if ($opts['ex'] === null) {
            if ($this->lastException && $this->lastExceptionIndex !== null) {
                $index = $this->lastExceptionIndex + 1;
            } else {
                $index = 0;
            }
        } else {
            $index = \max(0, \intval($input->getOption('ex')) - 1);
        }
        $trace = $exception->getTrace();
        \array_unshift($trace, [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
        if ($index >= \count($trace)) {
            $index = 0;
        }
        $this->lastException = $exception;
        $this->lastExceptionIndex = $index;
        $output->writeln($this->getApplication()->formatException($exception));
        $output->writeln('--');
        $this->writeTraceLine($output, $trace, $index);
        $this->writeTraceCodeSnippet($output, $trace, $index);
        $this->setCommandScopeVariablesFromContext($trace[$index]);
    }
    private function writeTraceLine(OutputInterface $output, array $trace, $index)
    {
        $file = isset($trace[$index]['file']) ? $this->replaceCwd($trace[$index]['file']) : 'n/a';
        $line = isset($trace[$index]['line']) ? $trace[$index]['line'] : 'n/a';
        $output->writeln(\sprintf(
            'From <info>%s:%d</info> at <strong>level %d</strong> of backtrace (of %d).',
            OutputFormatter::escape($file),
            OutputFormatter::escape($line),
            $index + 1,
            \count($trace)
        ));
    }
    private function replaceCwd($file)
    {
        if ($cwd = \getcwd()) {
            $cwd = \rtrim($cwd, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
        if ($cwd === false) {
            return $file;
        } else {
            return \preg_replace('/^' . \preg_quote($cwd, '/') . '/', '', $file);
        }
    }
    private function writeTraceCodeSnippet(OutputInterface $output, array $trace, $index)
    {
        if (!isset($trace[$index]['file'])) {
            return;
        }
        $file = $trace[$index]['file'];
        if ($fileAndLine = $this->extractEvalFileAndLine($file)) {
            list($file, $line) = $fileAndLine;
        } else {
            if (!isset($trace[$index]['line'])) {
                return;
            }
            $line = $trace[$index]['line'];
        }
        if (\is_file($file)) {
            $code = @\file_get_contents($file);
        }
        if (empty($code)) {
            return;
        }
        $output->write($this->getHighlighter()->getCodeSnippet($code, $line, 5, 5), ShellOutput::OUTPUT_RAW);
    }
    private function getHighlighter()
    {
        if (!$this->highlighter) {
            $factory = new ConsoleColorFactory($this->colorMode);
            $this->highlighter = new Highlighter($factory->getConsoleColor());
        }
        return $this->highlighter;
    }
    private function setCommandScopeVariablesFromContext(array $context)
    {
        $vars = [];
        if (isset($context['class'])) {
            $vars['__class'] = $context['class'];
            if (isset($context['function'])) {
                $vars['__method'] = $context['function'];
            }
            try {
                $refl = new \ReflectionClass($context['class']);
                if ($namespace = $refl->getNamespaceName()) {
                    $vars['__namespace'] = $namespace;
                }
            } catch (\Exception $e) {
            }
        } elseif (isset($context['function'])) {
            $vars['__function'] = $context['function'];
            try {
                $refl = new \ReflectionFunction($context['function']);
                if ($namespace = $refl->getNamespaceName()) {
                    $vars['__namespace'] = $namespace;
                }
            } catch (\Exception $e) {
            }
        }
        if (isset($context['file'])) {
            $file = $context['file'];
            if ($fileAndLine = $this->extractEvalFileAndLine($file)) {
                list($file, $line) = $fileAndLine;
            } elseif (isset($context['line'])) {
                $line = $context['line'];
            }
            if (\is_file($file)) {
                $vars['__file'] = $file;
                if (isset($line)) {
                    $vars['__line'] = $line;
                }
                $vars['__dir'] = \dirname($file);
            }
        }
        $this->context->setCommandScopeVariables($vars);
    }
    private function extractEvalFileAndLine($file)
    {
        if (\preg_match('/(.*)\\((\\d+)\\) : eval\\(\\)\'d code$/', $file, $matches)) {
            return [$matches[1], $matches[2]];
        }
    }
}
