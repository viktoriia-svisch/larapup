<?php
namespace Symfony\Component\Process;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Pipes\PipesInterface;
use Symfony\Component\Process\Pipes\UnixPipes;
use Symfony\Component\Process\Pipes\WindowsPipes;
class Process implements \IteratorAggregate
{
    const ERR = 'err';
    const OUT = 'out';
    const STATUS_READY = 'ready';
    const STATUS_STARTED = 'started';
    const STATUS_TERMINATED = 'terminated';
    const STDIN = 0;
    const STDOUT = 1;
    const STDERR = 2;
    const TIMEOUT_PRECISION = 0.2;
    const ITER_NON_BLOCKING = 1; 
    const ITER_KEEP_OUTPUT = 2;  
    const ITER_SKIP_OUT = 4;     
    const ITER_SKIP_ERR = 8;     
    private $callback;
    private $hasCallback = false;
    private $commandline;
    private $cwd;
    private $env;
    private $input;
    private $starttime;
    private $lastOutputTime;
    private $timeout;
    private $idleTimeout;
    private $exitcode;
    private $fallbackStatus = [];
    private $processInformation;
    private $outputDisabled = false;
    private $stdout;
    private $stderr;
    private $process;
    private $status = self::STATUS_READY;
    private $incrementalOutputOffset = 0;
    private $incrementalErrorOutputOffset = 0;
    private $tty;
    private $pty;
    private $useFileHandles = false;
    private $processPipes;
    private $latestSignal;
    private static $sigchild;
    public static $exitCodes = [
        0 => 'OK',
        1 => 'General error',
        2 => 'Misuse of shell builtins',
        126 => 'Invoked command cannot execute',
        127 => 'Command not found',
        128 => 'Invalid exit argument',
        129 => 'Hangup',
        130 => 'Interrupt',
        131 => 'Quit and dump core',
        132 => 'Illegal instruction',
        133 => 'Trace/breakpoint trap',
        134 => 'Process aborted',
        135 => 'Bus error: "access to undefined portion of memory object"',
        136 => 'Floating point exception: "erroneous arithmetic operation"',
        137 => 'Kill (terminate immediately)',
        138 => 'User-defined 1',
        139 => 'Segmentation violation',
        140 => 'User-defined 2',
        141 => 'Write to pipe with no one reading',
        142 => 'Signal raised by alarm',
        143 => 'Termination (request to terminate)',
        145 => 'Child process terminated, stopped (or continued*)',
        146 => 'Continue if stopped',
        147 => 'Stop executing temporarily',
        148 => 'Terminal stop signal',
        149 => 'Background process attempting to read from tty ("in")',
        150 => 'Background process attempting to write to tty ("out")',
        151 => 'Urgent data available on socket',
        152 => 'CPU time limit exceeded',
        153 => 'File size limit exceeded',
        154 => 'Signal raised by timer counting virtual time: "virtual timer expired"',
        155 => 'Profiling timer expired',
        157 => 'Pollable event',
        159 => 'Bad syscall',
    ];
    public function __construct($command, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60)
    {
        if (!\function_exists('proc_open')) {
            throw new LogicException('The Process class relies on proc_open, which is not available on your PHP installation.');
        }
        if (!\is_array($command)) {
            @trigger_error(sprintf('Passing a command as string when creating a "%s" instance is deprecated since Symfony 4.2, pass it as an array of its arguments instead, or use the "Process::fromShellCommandline()" constructor if you need features provided by the shell.', __CLASS__), E_USER_DEPRECATED);
        }
        $this->commandline = $command;
        $this->cwd = $cwd;
        if (null === $this->cwd && (\defined('ZEND_THREAD_SAFE') || '\\' === \DIRECTORY_SEPARATOR)) {
            $this->cwd = getcwd();
        }
        if (null !== $env) {
            $this->setEnv($env);
        }
        $this->setInput($input);
        $this->setTimeout($timeout);
        $this->useFileHandles = '\\' === \DIRECTORY_SEPARATOR;
        $this->pty = false;
    }
    public static function fromShellCommandline(string $command, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60)
    {
        $process = new static([], $cwd, $env, $input, $timeout);
        $process->commandline = $command;
        return $process;
    }
    public function __destruct()
    {
        $this->stop(0);
    }
    public function __clone()
    {
        $this->resetProcessData();
    }
    public function run(callable $callback = null, array $env = []): int
    {
        $this->start($callback, $env);
        return $this->wait();
    }
    public function mustRun(callable $callback = null, array $env = [])
    {
        if (0 !== $this->run($callback, $env)) {
            throw new ProcessFailedException($this);
        }
        return $this;
    }
    public function start(callable $callback = null, array $env = [])
    {
        if ($this->isRunning()) {
            throw new RuntimeException('Process is already running');
        }
        $this->resetProcessData();
        $this->starttime = $this->lastOutputTime = microtime(true);
        $this->callback = $this->buildCallback($callback);
        $this->hasCallback = null !== $callback;
        $descriptors = $this->getDescriptors();
        if (\is_array($commandline = $this->commandline)) {
            $commandline = implode(' ', array_map([$this, 'escapeArgument'], $commandline));
            if ('\\' !== \DIRECTORY_SEPARATOR) {
                $commandline = 'exec '.$commandline;
            }
        }
        if ($this->env) {
            $env += $this->env;
        }
        $env += $this->getDefaultEnv();
        $options = ['suppress_errors' => true];
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $options['bypass_shell'] = true;
            $commandline = $this->prepareWindowsCommandLine($commandline, $env);
        } elseif (!$this->useFileHandles && $this->isSigchildEnabled()) {
            $descriptors[3] = ['pipe', 'w'];
            $commandline = '{ ('.$commandline.') <&3 3<&- 3>/dev/null & } 3<&0;';
            $commandline .= 'pid=$!; echo $pid >&3; wait $pid; code=$?; echo $code >&3; exit $code';
            $ptsWorkaround = fopen(__FILE__, 'r');
        }
        $envPairs = [];
        foreach ($env as $k => $v) {
            if (false !== $v) {
                $envPairs[] = $k.'='.$v;
            }
        }
        if (!is_dir($this->cwd)) {
            throw new RuntimeException('The provided cwd does not exist.');
        }
        $this->process = proc_open($commandline, $descriptors, $this->processPipes->pipes, $this->cwd, $envPairs, $options);
        if (!\is_resource($this->process)) {
            throw new RuntimeException('Unable to launch a new process.');
        }
        $this->status = self::STATUS_STARTED;
        if (isset($descriptors[3])) {
            $this->fallbackStatus['pid'] = (int) fgets($this->processPipes->pipes[3]);
        }
        if ($this->tty) {
            return;
        }
        $this->updateStatus(false);
        $this->checkTimeout();
    }
    public function restart(callable $callback = null, array $env = [])
    {
        if ($this->isRunning()) {
            throw new RuntimeException('Process is already running');
        }
        $process = clone $this;
        $process->start($callback, $env);
        return $process;
    }
    public function wait(callable $callback = null)
    {
        $this->requireProcessIsStarted(__FUNCTION__);
        $this->updateStatus(false);
        if (null !== $callback) {
            if (!$this->processPipes->haveReadSupport()) {
                $this->stop(0);
                throw new \LogicException('Pass the callback to the "Process::start" method or call enableOutput to use a callback with "Process::wait"');
            }
            $this->callback = $this->buildCallback($callback);
        }
        do {
            $this->checkTimeout();
            $running = '\\' === \DIRECTORY_SEPARATOR ? $this->isRunning() : $this->processPipes->areOpen();
            $this->readPipes($running, '\\' !== \DIRECTORY_SEPARATOR || !$running);
        } while ($running);
        while ($this->isRunning()) {
            usleep(1000);
        }
        if ($this->processInformation['signaled'] && $this->processInformation['termsig'] !== $this->latestSignal) {
            throw new ProcessSignaledException($this);
        }
        return $this->exitcode;
    }
    public function waitUntil(callable $callback): bool
    {
        $this->requireProcessIsStarted(__FUNCTION__);
        $this->updateStatus(false);
        if (!$this->processPipes->haveReadSupport()) {
            $this->stop(0);
            throw new \LogicException('Pass the callback to the "Process::start" method or call enableOutput to use a callback with "Process::waitUntil".');
        }
        $callback = $this->buildCallback($callback);
        $ready = false;
        while (true) {
            $this->checkTimeout();
            $running = '\\' === \DIRECTORY_SEPARATOR ? $this->isRunning() : $this->processPipes->areOpen();
            $output = $this->processPipes->readAndWrite($running, '\\' !== \DIRECTORY_SEPARATOR || !$running);
            foreach ($output as $type => $data) {
                if (3 !== $type) {
                    $ready = $callback(self::STDOUT === $type ? self::OUT : self::ERR, $data) || $ready;
                } elseif (!isset($this->fallbackStatus['signaled'])) {
                    $this->fallbackStatus['exitcode'] = (int) $data;
                }
            }
            if ($ready) {
                return true;
            }
            if (!$running) {
                return false;
            }
            usleep(1000);
        }
    }
    public function getPid()
    {
        return $this->isRunning() ? $this->processInformation['pid'] : null;
    }
    public function signal($signal)
    {
        $this->doSignal($signal, true);
        return $this;
    }
    public function disableOutput()
    {
        if ($this->isRunning()) {
            throw new RuntimeException('Disabling output while the process is running is not possible.');
        }
        if (null !== $this->idleTimeout) {
            throw new LogicException('Output can not be disabled while an idle timeout is set.');
        }
        $this->outputDisabled = true;
        return $this;
    }
    public function enableOutput()
    {
        if ($this->isRunning()) {
            throw new RuntimeException('Enabling output while the process is running is not possible.');
        }
        $this->outputDisabled = false;
        return $this;
    }
    public function isOutputDisabled()
    {
        return $this->outputDisabled;
    }
    public function getOutput()
    {
        $this->readPipesForOutput(__FUNCTION__);
        if (false === $ret = stream_get_contents($this->stdout, -1, 0)) {
            return '';
        }
        return $ret;
    }
    public function getIncrementalOutput()
    {
        $this->readPipesForOutput(__FUNCTION__);
        $latest = stream_get_contents($this->stdout, -1, $this->incrementalOutputOffset);
        $this->incrementalOutputOffset = ftell($this->stdout);
        if (false === $latest) {
            return '';
        }
        return $latest;
    }
    public function getIterator($flags = 0)
    {
        $this->readPipesForOutput(__FUNCTION__, false);
        $clearOutput = !(self::ITER_KEEP_OUTPUT & $flags);
        $blocking = !(self::ITER_NON_BLOCKING & $flags);
        $yieldOut = !(self::ITER_SKIP_OUT & $flags);
        $yieldErr = !(self::ITER_SKIP_ERR & $flags);
        while (null !== $this->callback || ($yieldOut && !feof($this->stdout)) || ($yieldErr && !feof($this->stderr))) {
            if ($yieldOut) {
                $out = stream_get_contents($this->stdout, -1, $this->incrementalOutputOffset);
                if (isset($out[0])) {
                    if ($clearOutput) {
                        $this->clearOutput();
                    } else {
                        $this->incrementalOutputOffset = ftell($this->stdout);
                    }
                    yield self::OUT => $out;
                }
            }
            if ($yieldErr) {
                $err = stream_get_contents($this->stderr, -1, $this->incrementalErrorOutputOffset);
                if (isset($err[0])) {
                    if ($clearOutput) {
                        $this->clearErrorOutput();
                    } else {
                        $this->incrementalErrorOutputOffset = ftell($this->stderr);
                    }
                    yield self::ERR => $err;
                }
            }
            if (!$blocking && !isset($out[0]) && !isset($err[0])) {
                yield self::OUT => '';
            }
            $this->checkTimeout();
            $this->readPipesForOutput(__FUNCTION__, $blocking);
        }
    }
    public function clearOutput()
    {
        ftruncate($this->stdout, 0);
        fseek($this->stdout, 0);
        $this->incrementalOutputOffset = 0;
        return $this;
    }
    public function getErrorOutput()
    {
        $this->readPipesForOutput(__FUNCTION__);
        if (false === $ret = stream_get_contents($this->stderr, -1, 0)) {
            return '';
        }
        return $ret;
    }
    public function getIncrementalErrorOutput()
    {
        $this->readPipesForOutput(__FUNCTION__);
        $latest = stream_get_contents($this->stderr, -1, $this->incrementalErrorOutputOffset);
        $this->incrementalErrorOutputOffset = ftell($this->stderr);
        if (false === $latest) {
            return '';
        }
        return $latest;
    }
    public function clearErrorOutput()
    {
        ftruncate($this->stderr, 0);
        fseek($this->stderr, 0);
        $this->incrementalErrorOutputOffset = 0;
        return $this;
    }
    public function getExitCode()
    {
        $this->updateStatus(false);
        return $this->exitcode;
    }
    public function getExitCodeText()
    {
        if (null === $exitcode = $this->getExitCode()) {
            return;
        }
        return isset(self::$exitCodes[$exitcode]) ? self::$exitCodes[$exitcode] : 'Unknown error';
    }
    public function isSuccessful()
    {
        return 0 === $this->getExitCode();
    }
    public function hasBeenSignaled()
    {
        $this->requireProcessIsTerminated(__FUNCTION__);
        return $this->processInformation['signaled'];
    }
    public function getTermSignal()
    {
        $this->requireProcessIsTerminated(__FUNCTION__);
        if ($this->isSigchildEnabled() && -1 === $this->processInformation['termsig']) {
            throw new RuntimeException('This PHP has been compiled with --enable-sigchild. Term signal can not be retrieved.');
        }
        return $this->processInformation['termsig'];
    }
    public function hasBeenStopped()
    {
        $this->requireProcessIsTerminated(__FUNCTION__);
        return $this->processInformation['stopped'];
    }
    public function getStopSignal()
    {
        $this->requireProcessIsTerminated(__FUNCTION__);
        return $this->processInformation['stopsig'];
    }
    public function isRunning()
    {
        if (self::STATUS_STARTED !== $this->status) {
            return false;
        }
        $this->updateStatus(false);
        return $this->processInformation['running'];
    }
    public function isStarted()
    {
        return self::STATUS_READY != $this->status;
    }
    public function isTerminated()
    {
        $this->updateStatus(false);
        return self::STATUS_TERMINATED == $this->status;
    }
    public function getStatus()
    {
        $this->updateStatus(false);
        return $this->status;
    }
    public function stop($timeout = 10, $signal = null)
    {
        $timeoutMicro = microtime(true) + $timeout;
        if ($this->isRunning()) {
            $this->doSignal(15, false);
            do {
                usleep(1000);
            } while ($this->isRunning() && microtime(true) < $timeoutMicro);
            if ($this->isRunning()) {
                $this->doSignal($signal ?: 9, false);
            }
        }
        if ($this->isRunning()) {
            if (isset($this->fallbackStatus['pid'])) {
                unset($this->fallbackStatus['pid']);
                return $this->stop(0, $signal);
            }
            $this->close();
        }
        return $this->exitcode;
    }
    public function addOutput(string $line)
    {
        $this->lastOutputTime = microtime(true);
        fseek($this->stdout, 0, SEEK_END);
        fwrite($this->stdout, $line);
        fseek($this->stdout, $this->incrementalOutputOffset);
    }
    public function addErrorOutput(string $line)
    {
        $this->lastOutputTime = microtime(true);
        fseek($this->stderr, 0, SEEK_END);
        fwrite($this->stderr, $line);
        fseek($this->stderr, $this->incrementalErrorOutputOffset);
    }
    public function getCommandLine()
    {
        return \is_array($this->commandline) ? implode(' ', array_map([$this, 'escapeArgument'], $this->commandline)) : $this->commandline;
    }
    public function setCommandLine($commandline)
    {
        @trigger_error(sprintf('The "%s()" method is deprecated since Symfony 4.2.', __METHOD__), E_USER_DEPRECATED);
        $this->commandline = $commandline;
        return $this;
    }
    public function getTimeout()
    {
        return $this->timeout;
    }
    public function getIdleTimeout()
    {
        return $this->idleTimeout;
    }
    public function setTimeout($timeout)
    {
        $this->timeout = $this->validateTimeout($timeout);
        return $this;
    }
    public function setIdleTimeout($timeout)
    {
        if (null !== $timeout && $this->outputDisabled) {
            throw new LogicException('Idle timeout can not be set while the output is disabled.');
        }
        $this->idleTimeout = $this->validateTimeout($timeout);
        return $this;
    }
    public function setTty($tty)
    {
        if ('\\' === \DIRECTORY_SEPARATOR && $tty) {
            throw new RuntimeException('TTY mode is not supported on Windows platform.');
        }
        if ($tty && !self::isTtySupported()) {
            throw new RuntimeException('TTY mode requires /dev/tty to be read/writable.');
        }
        $this->tty = (bool) $tty;
        return $this;
    }
    public function isTty()
    {
        return $this->tty;
    }
    public function setPty($bool)
    {
        $this->pty = (bool) $bool;
        return $this;
    }
    public function isPty()
    {
        return $this->pty;
    }
    public function getWorkingDirectory()
    {
        if (null === $this->cwd) {
            return getcwd() ?: null;
        }
        return $this->cwd;
    }
    public function setWorkingDirectory($cwd)
    {
        $this->cwd = $cwd;
        return $this;
    }
    public function getEnv()
    {
        return $this->env;
    }
    public function setEnv(array $env)
    {
        $env = array_filter($env, function ($value) {
            return !\is_array($value);
        });
        $this->env = $env;
        return $this;
    }
    public function getInput()
    {
        return $this->input;
    }
    public function setInput($input)
    {
        if ($this->isRunning()) {
            throw new LogicException('Input can not be set while the process is running.');
        }
        $this->input = ProcessUtils::validateInput(__METHOD__, $input);
        return $this;
    }
    public function inheritEnvironmentVariables($inheritEnv = true)
    {
        if (!$inheritEnv) {
            throw new InvalidArgumentException('Not inheriting environment variables is not supported.');
        }
        return $this;
    }
    public function checkTimeout()
    {
        if (self::STATUS_STARTED !== $this->status) {
            return;
        }
        if (null !== $this->timeout && $this->timeout < microtime(true) - $this->starttime) {
            $this->stop(0);
            throw new ProcessTimedOutException($this, ProcessTimedOutException::TYPE_GENERAL);
        }
        if (null !== $this->idleTimeout && $this->idleTimeout < microtime(true) - $this->lastOutputTime) {
            $this->stop(0);
            throw new ProcessTimedOutException($this, ProcessTimedOutException::TYPE_IDLE);
        }
    }
    public static function isTtySupported(): bool
    {
        static $isTtySupported;
        if (null === $isTtySupported) {
            $isTtySupported = (bool) @proc_open('echo 1 >/dev/null', [['file', '/dev/tty', 'r'], ['file', '/dev/tty', 'w'], ['file', '/dev/tty', 'w']], $pipes);
        }
        return $isTtySupported;
    }
    public static function isPtySupported()
    {
        static $result;
        if (null !== $result) {
            return $result;
        }
        if ('\\' === \DIRECTORY_SEPARATOR) {
            return $result = false;
        }
        return $result = (bool) @proc_open('echo 1 >/dev/null', [['pty'], ['pty'], ['pty']], $pipes);
    }
    private function getDescriptors(): array
    {
        if ($this->input instanceof \Iterator) {
            $this->input->rewind();
        }
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->processPipes = new WindowsPipes($this->input, !$this->outputDisabled || $this->hasCallback);
        } else {
            $this->processPipes = new UnixPipes($this->isTty(), $this->isPty(), $this->input, !$this->outputDisabled || $this->hasCallback);
        }
        return $this->processPipes->getDescriptors();
    }
    protected function buildCallback(callable $callback = null)
    {
        if ($this->outputDisabled) {
            return function ($type, $data) use ($callback) {
                if (null !== $callback) {
                    return $callback($type, $data);
                }
            };
        }
        $out = self::OUT;
        return function ($type, $data) use ($callback, $out) {
            if ($out == $type) {
                $this->addOutput($data);
            } else {
                $this->addErrorOutput($data);
            }
            if (null !== $callback) {
                return $callback($type, $data);
            }
        };
    }
    protected function updateStatus($blocking)
    {
        if (self::STATUS_STARTED !== $this->status) {
            return;
        }
        $this->processInformation = proc_get_status($this->process);
        $running = $this->processInformation['running'];
        $this->readPipes($running && $blocking, '\\' !== \DIRECTORY_SEPARATOR || !$running);
        if ($this->fallbackStatus && $this->isSigchildEnabled()) {
            $this->processInformation = $this->fallbackStatus + $this->processInformation;
        }
        if (!$running) {
            $this->close();
        }
    }
    protected function isSigchildEnabled()
    {
        if (null !== self::$sigchild) {
            return self::$sigchild;
        }
        if (!\function_exists('phpinfo')) {
            return self::$sigchild = false;
        }
        ob_start();
        phpinfo(INFO_GENERAL);
        return self::$sigchild = false !== strpos(ob_get_clean(), '--enable-sigchild');
    }
    private function readPipesForOutput(string $caller, bool $blocking = false)
    {
        if ($this->outputDisabled) {
            throw new LogicException('Output has been disabled.');
        }
        $this->requireProcessIsStarted($caller);
        $this->updateStatus($blocking);
    }
    private function validateTimeout(?float $timeout): ?float
    {
        $timeout = (float) $timeout;
        if (0.0 === $timeout) {
            $timeout = null;
        } elseif ($timeout < 0) {
            throw new InvalidArgumentException('The timeout value must be a valid positive integer or float number.');
        }
        return $timeout;
    }
    private function readPipes(bool $blocking, bool $close)
    {
        $result = $this->processPipes->readAndWrite($blocking, $close);
        $callback = $this->callback;
        foreach ($result as $type => $data) {
            if (3 !== $type) {
                $callback(self::STDOUT === $type ? self::OUT : self::ERR, $data);
            } elseif (!isset($this->fallbackStatus['signaled'])) {
                $this->fallbackStatus['exitcode'] = (int) $data;
            }
        }
    }
    private function close(): int
    {
        $this->processPipes->close();
        if (\is_resource($this->process)) {
            proc_close($this->process);
        }
        $this->exitcode = $this->processInformation['exitcode'];
        $this->status = self::STATUS_TERMINATED;
        if (-1 === $this->exitcode) {
            if ($this->processInformation['signaled'] && 0 < $this->processInformation['termsig']) {
                $this->exitcode = 128 + $this->processInformation['termsig'];
            } elseif ($this->isSigchildEnabled()) {
                $this->processInformation['signaled'] = true;
                $this->processInformation['termsig'] = -1;
            }
        }
        $this->callback = null;
        return $this->exitcode;
    }
    private function resetProcessData()
    {
        $this->starttime = null;
        $this->callback = null;
        $this->exitcode = null;
        $this->fallbackStatus = [];
        $this->processInformation = null;
        $this->stdout = fopen('php:
        $this->stderr = fopen('php:
        $this->process = null;
        $this->latestSignal = null;
        $this->status = self::STATUS_READY;
        $this->incrementalOutputOffset = 0;
        $this->incrementalErrorOutputOffset = 0;
    }
    private function doSignal(int $signal, bool $throwException): bool
    {
        if (null === $pid = $this->getPid()) {
            if ($throwException) {
                throw new LogicException('Can not send signal on a non running process.');
            }
            return false;
        }
        if ('\\' === \DIRECTORY_SEPARATOR) {
            exec(sprintf('taskkill /F /T /PID %d 2>&1', $pid), $output, $exitCode);
            if ($exitCode && $this->isRunning()) {
                if ($throwException) {
                    throw new RuntimeException(sprintf('Unable to kill the process (%s).', implode(' ', $output)));
                }
                return false;
            }
        } else {
            if (!$this->isSigchildEnabled()) {
                $ok = @proc_terminate($this->process, $signal);
            } elseif (\function_exists('posix_kill')) {
                $ok = @posix_kill($pid, $signal);
            } elseif ($ok = proc_open(sprintf('kill -%d %d', $signal, $pid), [2 => ['pipe', 'w']], $pipes)) {
                $ok = false === fgets($pipes[2]);
            }
            if (!$ok) {
                if ($throwException) {
                    throw new RuntimeException(sprintf('Error while sending signal "%s".', $signal));
                }
                return false;
            }
        }
        $this->latestSignal = $signal;
        $this->fallbackStatus['signaled'] = true;
        $this->fallbackStatus['exitcode'] = -1;
        $this->fallbackStatus['termsig'] = $this->latestSignal;
        return true;
    }
    private function prepareWindowsCommandLine(string $cmd, array &$env)
    {
        $uid = uniqid('', true);
        $varCount = 0;
        $varCache = [];
        $cmd = preg_replace_callback(
            '/"(?:(
                [^"%!^]*+
                (?:
                    (?: !LF! | "(?:\^[%!^])?+" )
                    [^"%!^]*+
                )++
            ) | [^"]*+ )"/x',
            function ($m) use (&$env, &$varCache, &$varCount, $uid) {
                if (!isset($m[1])) {
                    return $m[0];
                }
                if (isset($varCache[$m[0]])) {
                    return $varCache[$m[0]];
                }
                if (false !== strpos($value = $m[1], "\0")) {
                    $value = str_replace("\0", '?', $value);
                }
                if (false === strpbrk($value, "\"%!\n")) {
                    return '"'.$value.'"';
                }
                $value = str_replace(['!LF!', '"^!"', '"^%"', '"^^"', '""'], ["\n", '!', '%', '^', '"'], $value);
                $value = '"'.preg_replace('/(\\\\*)"/', '$1$1\\"', $value).'"';
                $var = $uid.++$varCount;
                $env[$var] = $value;
                return $varCache[$m[0]] = '!'.$var.'!';
            },
            $cmd
        );
        $cmd = 'cmd /V:ON /E:ON /D /C ('.str_replace("\n", ' ', $cmd).')';
        foreach ($this->processPipes->getFiles() as $offset => $filename) {
            $cmd .= ' '.$offset.'>"'.$filename.'"';
        }
        return $cmd;
    }
    private function requireProcessIsStarted(string $functionName)
    {
        if (!$this->isStarted()) {
            throw new LogicException(sprintf('Process must be started before calling %s.', $functionName));
        }
    }
    private function requireProcessIsTerminated(string $functionName)
    {
        if (!$this->isTerminated()) {
            throw new LogicException(sprintf('Process must be terminated before calling %s.', $functionName));
        }
    }
    private function escapeArgument(?string $argument): string
    {
        if ('' === $argument || null === $argument) {
            return '""';
        }
        if ('\\' !== \DIRECTORY_SEPARATOR) {
            return "'".str_replace("'", "'\\''", $argument)."'";
        }
        if (false !== strpos($argument, "\0")) {
            $argument = str_replace("\0", '?', $argument);
        }
        if (!preg_match('/[\/()%!^"<>&|\s]/', $argument)) {
            return $argument;
        }
        $argument = preg_replace('/(\\\\+)$/', '$1$1', $argument);
        return '"'.str_replace(['"', '^', '%', '!', "\n"], ['""', '"^^"', '"^%"', '"^!"', '!LF!'], $argument).'"';
    }
    private function getDefaultEnv()
    {
        $env = [];
        foreach ($_SERVER as $k => $v) {
            if (\is_string($v) && false !== $v = getenv($k)) {
                $env[$k] = $v;
            }
        }
        foreach ($_ENV as $k => $v) {
            if (\is_string($v)) {
                $env[$k] = $v;
            }
        }
        return $env;
    }
}
