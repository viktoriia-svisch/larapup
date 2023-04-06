<?php
namespace Composer\Util;
use Composer\IO\IOInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;
class ProcessExecutor
{
    protected static $timeout = 300;
    protected $captureOutput;
    protected $errorOutput;
    protected $io;
    public function __construct(IOInterface $io = null)
    {
        $this->io = $io;
    }
    public function execute($command, &$output = null, $cwd = null)
    {
        if ($this->io && $this->io->isDebug()) {
            $safeCommand = preg_replace_callback('{:
                if (preg_match('{^[a-f0-9]{12,}$}', $m['user'])) {
                    return ':
                }
                return ':
            }, $command);
            $this->io->writeError('Executing command ('.($cwd ?: 'CWD').'): '.$safeCommand);
        }
        if (null === $cwd && Platform::isWindows() && false !== strpos($command, 'git') && getcwd()) {
            $cwd = realpath(getcwd());
        }
        $this->captureOutput = func_num_args() > 1;
        $this->errorOutput = null;
        if (method_exists('Symfony\Component\Process\Process', 'fromShellCommandline')) {
            $process = Process::fromShellCommandline($command, $cwd, null, null, static::getTimeout());
        } else {
            $process = new Process($command, $cwd, null, null, static::getTimeout());
        }
        $callback = is_callable($output) ? $output : array($this, 'outputHandler');
        $process->run($callback);
        if ($this->captureOutput && !is_callable($output)) {
            $output = $process->getOutput();
        }
        $this->errorOutput = $process->getErrorOutput();
        return $process->getExitCode();
    }
    public function splitLines($output)
    {
        $output = trim($output);
        return ((string) $output === '') ? array() : preg_split('{\r?\n}', $output);
    }
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }
    public function outputHandler($type, $buffer)
    {
        if ($this->captureOutput) {
            return;
        }
        if (null === $this->io) {
            echo $buffer;
            return;
        }
        if (Process::ERR === $type) {
            $this->io->writeError($buffer, false);
        } else {
            $this->io->write($buffer, false);
        }
    }
    public static function getTimeout()
    {
        return static::$timeout;
    }
    public static function setTimeout($timeout)
    {
        static::$timeout = $timeout;
    }
    public static function escape($argument)
    {
        return self::escapeArgument($argument);
    }
    private static function escapeArgument($argument)
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            if ('' === $argument) {
                return escapeshellarg($argument);
            }
            $escapedArgument = '';
            $quote = false;
            foreach (preg_split('/(")/', $argument, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $part) {
                if ('"' === $part) {
                    $escapedArgument .= '\\"';
                } elseif (self::isSurroundedBy($part, '%')) {
                    $escapedArgument .= '^%"'.substr($part, 1, -1).'"^%';
                } else {
                    if ('\\' === substr($part, -1)) {
                        $part .= '\\';
                    }
                    $quote = true;
                    $escapedArgument .= $part;
                }
            }
            if ($quote) {
                $escapedArgument = '"'.$escapedArgument.'"';
            }
            return $escapedArgument;
        }
        return "'".str_replace("'", "'\\''", $argument)."'";
    }
    private static function isSurroundedBy($arg, $char)
    {
        return 2 < strlen($arg) && $char === $arg[0] && $char === $arg[strlen($arg) - 1];
    }
}
