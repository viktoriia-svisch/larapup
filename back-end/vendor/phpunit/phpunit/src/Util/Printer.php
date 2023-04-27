<?php
namespace PHPUnit\Util;
use PHPUnit\Framework\Exception;
class Printer
{
    protected $autoFlush = false;
    protected $out;
    protected $outTarget;
    public function __construct($out = null)
    {
        if ($out !== null) {
            if (\is_string($out)) {
                if (\strpos($out, 'socket:
                    $out = \explode(':', \str_replace('socket:
                    if (\count($out) !== 2) {
                        throw new Exception;
                    }
                    $this->out = \fsockopen($out[0], $out[1]);
                } else {
                    if (\strpos($out, 'php:
                        throw new Exception(\sprintf('Directory "%s" was not created', \dirname($out)));
                    }
                    $this->out = \fopen($out, 'wt');
                }
                $this->outTarget = $out;
            } else {
                $this->out = $out;
            }
        }
    }
    public function flush(): void
    {
        if ($this->out && \strncmp($this->outTarget, 'php:
            \fclose($this->out);
        }
    }
    public function incrementalFlush(): void
    {
        if ($this->out) {
            \fflush($this->out);
        } else {
            \flush();
        }
    }
    public function write(string $buffer): void
    {
        if ($this->out) {
            \fwrite($this->out, $buffer);
            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        } else {
            if (\PHP_SAPI !== 'cli' && \PHP_SAPI !== 'phpdbg') {
                $buffer = \htmlspecialchars($buffer, \ENT_SUBSTITUTE);
            }
            print $buffer;
            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        }
    }
    public function getAutoFlush(): bool
    {
        return $this->autoFlush;
    }
    public function setAutoFlush(bool $autoFlush): void
    {
        $this->autoFlush = $autoFlush;
    }
}
