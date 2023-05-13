<?php
namespace Psy\ExecutionLoop;
use Psy\Context;
use Psy\Exception\BreakException;
use Psy\Shell;
class ProcessForker extends AbstractListener
{
    private $savegame;
    private $up;
    public static function isSupported()
    {
        return \function_exists('pcntl_signal') && \function_exists('posix_getpid');
    }
    public function beforeRun(Shell $shell)
    {
        list($up, $down) = \stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if (!$up) {
            throw new \RuntimeException('Unable to create socket pair');
        }
        $pid = \pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException('Unable to start execution loop');
        } elseif ($pid > 0) {
            \fclose($up);
            $read   = [$down];
            $write  = null;
            $except = null;
            do {
                $n = @\stream_select($read, $write, $except, null);
                if ($n === 0) {
                    throw new \RuntimeException('Process timed out waiting for execution loop');
                }
                if ($n === false) {
                    $err = \error_get_last();
                    if (!isset($err['message']) || \stripos($err['message'], 'interrupted system call') === false) {
                        $msg = $err['message'] ?
                            \sprintf('Error waiting for execution loop: %s', $err['message']) :
                            'Error waiting for execution loop';
                        throw new \RuntimeException($msg);
                    }
                }
            } while ($n < 1);
            $content = \stream_get_contents($down);
            \fclose($down);
            if ($content) {
                $shell->setScopeVariables(@\unserialize($content));
            }
            throw new BreakException('Exiting main thread');
        }
        if (\function_exists('setproctitle')) {
            setproctitle('psysh (loop)');
        }
        \fclose($down);
        $this->up = $up;
    }
    public function beforeLoop(Shell $shell)
    {
        $this->createSavegame();
    }
    public function afterLoop(Shell $shell)
    {
        if (isset($this->savegame)) {
            \posix_kill($this->savegame, SIGKILL);
            \pcntl_signal_dispatch();
        }
    }
    public function afterRun(Shell $shell)
    {
        if (isset($this->up)) {
            \fwrite($this->up, $this->serializeReturn($shell->getScopeVariables(false)));
            \fclose($this->up);
            \posix_kill(\posix_getpid(), SIGKILL);
        }
    }
    private function createSavegame()
    {
        $this->savegame = \posix_getpid();
        $pid = \pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException('Unable to create savegame fork');
        } elseif ($pid > 0) {
            \pcntl_waitpid($pid, $status);
            if (!\pcntl_wexitstatus($status)) {
                \posix_kill(\posix_getpid(), SIGKILL);
            }
            $this->createSavegame();
        }
    }
    private function serializeReturn(array $return)
    {
        $serializable = [];
        foreach ($return as $key => $value) {
            if (Context::isSpecialVariableName($key)) {
                continue;
            }
            if (\is_resource($value) || $value instanceof \Closure) {
                continue;
            }
            try {
                @\serialize($value);
                $serializable[$key] = $value;
            } catch (\Throwable $e) {
            } catch (\Exception $e) {
            }
        }
        return @\serialize($serializable);
    }
}
