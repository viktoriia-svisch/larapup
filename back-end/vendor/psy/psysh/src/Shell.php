<?php
namespace Psy;
use Psy\CodeCleaner\NoReturnValue;
use Psy\Exception\BreakException;
use Psy\Exception\ErrorException;
use Psy\Exception\Exception as PsyException;
use Psy\Exception\ThrowUpException;
use Psy\Exception\TypeErrorException;
use Psy\ExecutionLoop\ProcessForker;
use Psy\ExecutionLoop\RunkitReloader;
use Psy\Input\ShellInput;
use Psy\Input\SilentInput;
use Psy\Output\ShellOutput;
use Psy\TabCompletion\Matcher;
use Psy\VarDumper\PresenterAware;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
class Shell extends Application
{
    const VERSION = 'v0.9.9';
    const PROMPT      = '>>> ';
    const BUFF_PROMPT = '... ';
    const REPLAY      = '--> ';
    const RETVAL      = '=> ';
    private $config;
    private $cleaner;
    private $output;
    private $readline;
    private $inputBuffer;
    private $code;
    private $codeBuffer;
    private $codeBufferOpen;
    private $codeStack;
    private $stdoutBuffer;
    private $context;
    private $includes;
    private $loop;
    private $outputWantsNewline = false;
    private $prompt;
    private $loopListeners;
    private $autoCompleter;
    private $matchers = [];
    private $commandsMatcher;
    private $lastExecSuccess = true;
    public function __construct(Configuration $config = null)
    {
        $this->config        = $config ?: new Configuration();
        $this->cleaner       = $this->config->getCodeCleaner();
        $this->loop          = new ExecutionLoop();
        $this->context       = new Context();
        $this->includes      = [];
        $this->readline      = $this->config->getReadline();
        $this->inputBuffer   = [];
        $this->codeStack     = [];
        $this->stdoutBuffer  = '';
        $this->loopListeners = $this->getDefaultLoopListeners();
        parent::__construct('Psy Shell', self::VERSION);
        $this->config->setShell($this);
        \Psy\info($this->config);
    }
    public static function isIncluded(array $trace)
    {
        return isset($trace[0]['function']) &&
          \in_array($trace[0]['function'], ['require', 'include', 'require_once', 'include_once']);
    }
    public static function debug(array $vars = [], $bindTo = null)
    {
        return \Psy\debug($vars, $bindTo);
    }
    public function add(BaseCommand $command)
    {
        if ($ret = parent::add($command)) {
            if ($ret instanceof ContextAware) {
                $ret->setContext($this->context);
            }
            if ($ret instanceof PresenterAware) {
                $ret->setPresenter($this->config->getPresenter());
            }
            if (isset($this->commandsMatcher)) {
                $this->commandsMatcher->setCommands($this->all());
            }
        }
        return $ret;
    }
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
        ]);
    }
    protected function getDefaultCommands()
    {
        $sudo = new Command\SudoCommand();
        $sudo->setReadline($this->readline);
        $hist = new Command\HistoryCommand();
        $hist->setReadline($this->readline);
        return [
            new Command\HelpCommand(),
            new Command\ListCommand(),
            new Command\DumpCommand(),
            new Command\DocCommand(),
            new Command\ShowCommand($this->config->colorMode()),
            new Command\WtfCommand($this->config->colorMode()),
            new Command\WhereamiCommand($this->config->colorMode()),
            new Command\ThrowUpCommand(),
            new Command\TimeitCommand(),
            new Command\TraceCommand(),
            new Command\BufferCommand(),
            new Command\ClearCommand(),
            new Command\EditCommand($this->config->getRuntimeDir()),
            $sudo,
            $hist,
            new Command\ExitCommand(),
        ];
    }
    protected function getDefaultMatchers()
    {
        $this->commandsMatcher = new Matcher\CommandsMatcher($this->all());
        return [
            $this->commandsMatcher,
            new Matcher\KeywordsMatcher(),
            new Matcher\VariablesMatcher(),
            new Matcher\ConstantsMatcher(),
            new Matcher\FunctionsMatcher(),
            new Matcher\ClassNamesMatcher(),
            new Matcher\ClassMethodsMatcher(),
            new Matcher\ClassAttributesMatcher(),
            new Matcher\ObjectMethodsMatcher(),
            new Matcher\ObjectAttributesMatcher(),
            new Matcher\ClassMethodDefaultParametersMatcher(),
            new Matcher\ObjectMethodDefaultParametersMatcher(),
            new Matcher\FunctionDefaultParametersMatcher(),
        ];
    }
    protected function getTabCompletionMatchers()
    {
        @\trigger_error('getTabCompletionMatchers is no longer used', E_USER_DEPRECATED);
    }
    protected function getDefaultLoopListeners()
    {
        $listeners = [];
        if (ProcessForker::isSupported() && $this->config->usePcntl()) {
            $listeners[] = new ProcessForker();
        }
        if (RunkitReloader::isSupported()) {
            $listeners[] = new RunkitReloader();
        }
        return $listeners;
    }
    public function addMatchers(array $matchers)
    {
        $this->matchers = \array_merge($this->matchers, $matchers);
        if (isset($this->autoCompleter)) {
            $this->addMatchersToAutoCompleter($matchers);
        }
    }
    public function addTabCompletionMatchers(array $matchers)
    {
        $this->addMatchers($matchers);
    }
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->initializeTabCompletion();
        if ($input === null && !isset($_SERVER['argv'])) {
            $input = new ArgvInput([]);
        }
        if ($output === null) {
            $output = $this->config->getOutput();
        }
        try {
            return parent::run($input, $output);
        } catch (\Exception $e) {
            $this->writeException($e);
        }
        return 1;
    }
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->setOutput($output);
        $this->resetCodeBuffer();
        $this->setAutoExit(false);
        $this->setCatchExceptions(false);
        $this->readline->readHistory();
        $this->output->writeln($this->getHeader());
        $this->writeVersionInfo();
        $this->writeStartupMessage();
        try {
            $this->beforeRun();
            $this->loop->run($this);
            $this->afterRun();
        } catch (ThrowUpException $e) {
            throw $e->getPrevious();
        } catch (BreakException $e) {
            return;
        }
    }
    public function getInput()
    {
        $this->codeBufferOpen = false;
        do {
            $this->output->setVerbosity(ShellOutput::VERBOSITY_VERBOSE);
            $input = $this->readline();
            if ($input === false) {
                $this->output->writeln('');
                if ($this->hasCode()) {
                    $this->resetCodeBuffer();
                } else {
                    throw new BreakException('Ctrl+D');
                }
            }
            if (\trim($input) === '' && !$this->codeBufferOpen) {
                continue;
            }
            $input = $this->onInput($input);
            if ($this->hasCommand($input) && !$this->inputInOpenStringOrComment($input)) {
                $this->addHistory($input);
                $this->runCommand($input);
                continue;
            }
            $this->addCode($input);
        } while (!$this->hasValidCode());
    }
    private function inputInOpenStringOrComment($input)
    {
        if (!$this->hasCode()) {
            return;
        }
        $code = $this->codeBuffer;
        \array_push($code, $input);
        $tokens = @\token_get_all('<?php ' . \implode("\n", $code));
        $last = \array_pop($tokens);
        return $last === '"' || $last === '`' ||
            (\is_array($last) && \in_array($last[0], [T_ENCAPSED_AND_WHITESPACE, T_START_HEREDOC, T_COMMENT]));
    }
    protected function beforeRun()
    {
        foreach ($this->loopListeners as $listener) {
            $listener->beforeRun($this);
        }
    }
    public function beforeLoop()
    {
        foreach ($this->loopListeners as $listener) {
            $listener->beforeLoop($this);
        }
    }
    public function onInput($input)
    {
        foreach ($this->loopListeners as $listeners) {
            if (($return = $listeners->onInput($this, $input)) !== null) {
                $input = $return;
            }
        }
        return $input;
    }
    public function onExecute($code)
    {
        foreach ($this->loopListeners as $listener) {
            if (($return = $listener->onExecute($this, $code)) !== null) {
                $code = $return;
            }
        }
        return $code;
    }
    public function afterLoop()
    {
        foreach ($this->loopListeners as $listener) {
            $listener->afterLoop($this);
        }
    }
    protected function afterRun()
    {
        foreach ($this->loopListeners as $listener) {
            $listener->afterRun($this);
        }
    }
    public function setScopeVariables(array $vars)
    {
        $this->context->setAll($vars);
    }
    public function getScopeVariables($includeBoundObject = true)
    {
        $vars = $this->context->getAll();
        if (!$includeBoundObject) {
            unset($vars['this']);
        }
        return $vars;
    }
    public function getSpecialScopeVariables($includeBoundObject = true)
    {
        $vars = $this->context->getSpecialVariables();
        if (!$includeBoundObject) {
            unset($vars['this']);
        }
        return $vars;
    }
    public function getScopeVariablesDiff(array $currentVars)
    {
        $newVars = [];
        foreach ($this->getScopeVariables(false) as $key => $value) {
            if (!array_key_exists($key, $currentVars) || $currentVars[$key] !== $value) {
                $newVars[$key] = $value;
            }
        }
        return $newVars;
    }
    public function getUnusedCommandScopeVariableNames()
    {
        return $this->context->getUnusedCommandScopeVariableNames();
    }
    public function getScopeVariableNames()
    {
        return \array_keys($this->context->getAll());
    }
    public function getScopeVariable($name)
    {
        return $this->context->get($name);
    }
    public function setBoundObject($boundObject)
    {
        $this->context->setBoundObject($boundObject);
    }
    public function getBoundObject()
    {
        return $this->context->getBoundObject();
    }
    public function setBoundClass($boundClass)
    {
        $this->context->setBoundClass($boundClass);
    }
    public function getBoundClass()
    {
        return $this->context->getBoundClass();
    }
    public function setIncludes(array $includes = [])
    {
        $this->includes = $includes;
    }
    public function getIncludes()
    {
        return \array_merge($this->config->getDefaultIncludes(), $this->includes);
    }
    public function hasCode()
    {
        return !empty($this->codeBuffer);
    }
    protected function hasValidCode()
    {
        return !$this->codeBufferOpen && $this->code !== false;
    }
    public function addCode($code, $silent = false)
    {
        try {
            if (\substr(\rtrim($code), -1) === '\\') {
                $this->codeBufferOpen = true;
                $code = \substr(\rtrim($code), 0, -1);
            } else {
                $this->codeBufferOpen = false;
            }
            $this->codeBuffer[] = $silent ? new SilentInput($code) : $code;
            $this->code         = $this->cleaner->clean($this->codeBuffer, $this->config->requireSemicolons());
        } catch (\Exception $e) {
            $this->addCodeBufferToHistory();
            throw $e;
        }
    }
    private function setCode($code, $silent = false)
    {
        if ($this->hasCode()) {
            $this->codeStack[] = [$this->codeBuffer, $this->codeBufferOpen, $this->code];
        }
        $this->resetCodeBuffer();
        try {
            $this->addCode($code, $silent);
        } catch (\Throwable $e) {
            $this->popCodeStack();
            throw $e;
        } catch (\Exception $e) {
            $this->popCodeStack();
            throw $e;
        }
        if (!$this->hasValidCode()) {
            $this->popCodeStack();
            throw new \InvalidArgumentException('Unexpected end of input');
        }
    }
    public function getCodeBuffer()
    {
        return $this->codeBuffer;
    }
    protected function runCommand($input)
    {
        $command = $this->getCommand($input);
        if (empty($command)) {
            throw new \InvalidArgumentException('Command not found: ' . $input);
        }
        $input = new ShellInput(\str_replace('\\', '\\\\', \rtrim($input, " \t\n\r\0\x0B;")));
        if ($input->hasParameterOption(['--help', '-h'])) {
            $helpCommand = $this->get('help');
            $helpCommand->setCommand($command);
            return $helpCommand->run($input, $this->output);
        }
        return $command->run($input, $this->output);
    }
    public function resetCodeBuffer()
    {
        $this->codeBuffer = [];
        $this->code       = false;
    }
    public function addInput($input, $silent = false)
    {
        foreach ((array) $input as $line) {
            $this->inputBuffer[] = $silent ? new SilentInput($line) : $line;
        }
    }
    public function flushCode()
    {
        if ($this->hasValidCode()) {
            $this->addCodeBufferToHistory();
            $code = $this->code;
            $this->popCodeStack();
            return $code;
        }
    }
    private function popCodeStack()
    {
        $this->resetCodeBuffer();
        if (empty($this->codeStack)) {
            return;
        }
        list($codeBuffer, $codeBufferOpen, $code) = \array_pop($this->codeStack);
        $this->codeBuffer     = $codeBuffer;
        $this->codeBufferOpen = $codeBufferOpen;
        $this->code           = $code;
    }
    private function addHistory($line)
    {
        if ($line instanceof SilentInput) {
            return;
        }
        if (\trim($line) !== '' && \substr($line, 0, 1) !== ' ') {
            $this->readline->addHistory($line);
        }
    }
    private function addCodeBufferToHistory()
    {
        $codeBuffer = \array_filter($this->codeBuffer, function ($line) {
            return !$line instanceof SilentInput;
        });
        $this->addHistory(\implode("\n", $codeBuffer));
    }
    public function getNamespace()
    {
        if ($namespace = $this->cleaner->getNamespace()) {
            return \implode('\\', $namespace);
        }
    }
    public function writeStdout($out, $phase = PHP_OUTPUT_HANDLER_END)
    {
        $isCleaning = $phase & PHP_OUTPUT_HANDLER_CLEAN;
        if ($out !== '' && !$isCleaning) {
            $this->output->write($out, false, ShellOutput::OUTPUT_RAW);
            $this->outputWantsNewline = (\substr($out, -1) !== "\n");
            $this->stdoutBuffer .= $out;
        }
        if ($phase & PHP_OUTPUT_HANDLER_END) {
            if ($this->outputWantsNewline) {
                $this->output->writeln(\sprintf('<aside>%s</aside>', $this->config->useUnicode() ? '⏎' : '\\n'));
                $this->outputWantsNewline = false;
            }
            if ($this->stdoutBuffer !== '') {
                $this->context->setLastStdout($this->stdoutBuffer);
                $this->stdoutBuffer = '';
            }
        }
    }
    public function writeReturnValue($ret)
    {
        $this->lastExecSuccess = true;
        if ($ret instanceof NoReturnValue) {
            return;
        }
        $this->context->setReturnValue($ret);
        $ret    = $this->presentValue($ret);
        $indent = \str_repeat(' ', \strlen(static::RETVAL));
        $this->output->writeln(static::RETVAL . \str_replace(PHP_EOL, PHP_EOL . $indent, $ret));
    }
    public function writeException(\Exception $e)
    {
        $this->lastExecSuccess = false;
        $this->context->setLastException($e);
        $this->output->writeln($this->formatException($e));
        $this->resetCodeBuffer();
    }
    public function getLastExecSuccess()
    {
        return $this->lastExecSuccess;
    }
    public function formatException(\Exception $e)
    {
        $message = $e->getMessage();
        if (!$e instanceof PsyException) {
            if ($message === '') {
                $message = \get_class($e);
            } else {
                $message = \sprintf('%s with message \'%s\'', \get_class($e), $message);
            }
        }
        $message = \preg_replace(
            "#(\\w:)?(/\\w+)*/src/Execution(?:Loop)?Closure.php\(\d+\) : eval\(\)'d code#",
            "eval()'d code",
            \str_replace('\\', '/', $message)
        );
        $message = \str_replace(" in eval()'d code", ' in Psy Shell code', $message);
        $severity = ($e instanceof \ErrorException) ? $this->getSeverity($e) : 'error';
        return \sprintf('<%s>%s</%s>', $severity, OutputFormatter::escape($message), $severity);
    }
    protected function getSeverity(\ErrorException $e)
    {
        $severity = $e->getSeverity();
        if ($severity & \error_reporting()) {
            switch ($severity) {
                case E_WARNING:
                case E_NOTICE:
                case E_CORE_WARNING:
                case E_COMPILE_WARNING:
                case E_USER_WARNING:
                case E_USER_NOTICE:
                case E_STRICT:
                    return 'warning';
                default:
                    return 'error';
            }
        } else {
            return 'warning';
        }
    }
    public function execute($code, $throwExceptions = false)
    {
        $this->setCode($code, true);
        $closure = new ExecutionClosure($this);
        if ($throwExceptions) {
            return $closure->execute();
        }
        try {
            return $closure->execute();
        } catch (\TypeError $_e) {
            $this->writeException(TypeErrorException::fromTypeError($_e));
        } catch (\Error $_e) {
            $this->writeException(ErrorException::fromError($_e));
        } catch (\Exception $_e) {
            $this->writeException($_e);
        }
    }
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        if ($errno & \error_reporting()) {
            ErrorException::throwException($errno, $errstr, $errfile, $errline);
        } elseif ($errno & $this->config->errorLoggingLevel()) {
            $this->writeException(new ErrorException($errstr, 0, $errno, $errfile, $errline));
        }
    }
    protected function presentValue($val)
    {
        return $this->config->getPresenter()->present($val);
    }
    protected function getCommand($input)
    {
        $input = new StringInput($input);
        if ($name = $input->getFirstArgument()) {
            return $this->get($name);
        }
    }
    protected function hasCommand($input)
    {
        if (\preg_match('/([^\s]+?)(?:\s|$)/A', \ltrim($input), $match)) {
            return $this->has($match[1]);
        }
        return false;
    }
    protected function getPrompt()
    {
        if ($this->hasCode()) {
            return static::BUFF_PROMPT;
        }
        return $this->config->getPrompt() ?: static::PROMPT;
    }
    protected function readline()
    {
        if (!empty($this->inputBuffer)) {
            $line = \array_shift($this->inputBuffer);
            if (!$line instanceof SilentInput) {
                $this->output->writeln(\sprintf('<aside>%s %s</aside>', static::REPLAY, OutputFormatter::escape($line)));
            }
            return $line;
        }
        if ($bracketedPaste = $this->config->useBracketedPaste()) {
            \printf("\e[?2004h"); 
        }
        $line = $this->readline->readline($this->getPrompt());
        if ($bracketedPaste) {
            \printf("\e[?2004l"); 
        }
        return $line;
    }
    protected function getHeader()
    {
        return \sprintf('<aside>%s by Justin Hileman</aside>', $this->getVersion());
    }
    public function getVersion()
    {
        $separator = $this->config->useUnicode() ? '—' : '-';
        return \sprintf('Psy Shell %s (PHP %s %s %s)', self::VERSION, PHP_VERSION, $separator, PHP_SAPI);
    }
    public function getManualDb()
    {
        return $this->config->getManualDb();
    }
    protected function autocomplete($text)
    {
        @\trigger_error('Tab completion is provided by the AutoCompleter service', E_USER_DEPRECATED);
    }
    protected function initializeTabCompletion()
    {
        if (!$this->config->useTabCompletion()) {
            return;
        }
        $this->autoCompleter = $this->config->getAutoCompleter();
        $this->addMatchersToAutoCompleter($this->getDefaultMatchers());
        $this->addMatchersToAutoCompleter($this->matchers);
        $this->autoCompleter->activate();
    }
    private function addMatchersToAutoCompleter(array $matchers)
    {
        foreach ($matchers as $matcher) {
            if ($matcher instanceof ContextAware) {
                $matcher->setContext($this->context);
            }
            $this->autoCompleter->addMatcher($matcher);
        }
    }
    protected function writeVersionInfo()
    {
        if (PHP_SAPI !== 'cli') {
            return;
        }
        try {
            $client = $this->config->getChecker();
            if (!$client->isLatest()) {
                $this->output->writeln(\sprintf('New version is available (current: %s, latest: %s)', self::VERSION, $client->getLatest()));
            }
        } catch (\InvalidArgumentException $e) {
            $this->output->writeln($e->getMessage());
        }
    }
    protected function writeStartupMessage()
    {
        $message = $this->config->getStartupMessage();
        if ($message !== null && $message !== '') {
            $this->output->writeln($message);
        }
    }
}
