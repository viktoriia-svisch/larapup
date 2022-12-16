<?php
namespace Psy\Readline;
use Psy\Exception\BreakException;
class Transient implements Readline
{
    private $history;
    private $historySize;
    private $eraseDups;
    private $stdin;
    public static function isSupported()
    {
        return true;
    }
    public function __construct($historyFile = null, $historySize = 0, $eraseDups = false)
    {
        $this->history     = [];
        $this->historySize = $historySize;
        $this->eraseDups   = $eraseDups;
    }
    public function addHistory($line)
    {
        if ($this->eraseDups) {
            if (($key = \array_search($line, $this->history)) !== false) {
                unset($this->history[$key]);
            }
        }
        $this->history[] = $line;
        if ($this->historySize > 0) {
            $histsize = \count($this->history);
            if ($histsize > $this->historySize) {
                $this->history = \array_slice($this->history, $histsize - $this->historySize);
            }
        }
        $this->history = \array_values($this->history);
        return true;
    }
    public function clearHistory()
    {
        $this->history = [];
        return true;
    }
    public function listHistory()
    {
        return $this->history;
    }
    public function readHistory()
    {
        return true;
    }
    public function readline($prompt = null)
    {
        echo $prompt;
        return \rtrim(\fgets($this->getStdin()), "\n\r");
    }
    public function redisplay()
    {
    }
    public function writeHistory()
    {
        return true;
    }
    private function getStdin()
    {
        if (!isset($this->stdin)) {
            $this->stdin = \fopen('php:
        }
        if (\feof($this->stdin)) {
            throw new BreakException('Ctrl+D');
        }
        return $this->stdin;
    }
}
