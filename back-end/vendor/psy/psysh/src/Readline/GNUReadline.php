<?php
namespace Psy\Readline;
class GNUReadline implements Readline
{
    protected $historyFile;
    protected $historySize;
    protected $eraseDups;
    public static function isSupported()
    {
        return \function_exists('readline_list_history');
    }
    public function __construct($historyFile = null, $historySize = 0, $eraseDups = false)
    {
        $this->historyFile = ($historyFile !== null) ? $historyFile : false;
        $this->historySize = $historySize;
        $this->eraseDups   = $eraseDups;
    }
    public function addHistory($line)
    {
        if ($res = \readline_add_history($line)) {
            $this->writeHistory();
        }
        return $res;
    }
    public function clearHistory()
    {
        if ($res = \readline_clear_history()) {
            $this->writeHistory();
        }
        return $res;
    }
    public function listHistory()
    {
        return readline_list_history();
    }
    public function readHistory()
    {
        if (\version_compare(PHP_VERSION, '5.6.7', '>=') || !\ini_get('open_basedir')) {
            \readline_read_history();
        }
        \readline_clear_history();
        return \readline_read_history($this->historyFile);
    }
    public function readline($prompt = null)
    {
        return \readline($prompt);
    }
    public function redisplay()
    {
        \readline_redisplay();
    }
    public function writeHistory()
    {
        if ($this->historyFile !== false) {
            $res = \readline_write_history($this->historyFile);
        } else {
            $res = true;
        }
        if (!$res || !$this->eraseDups && !$this->historySize > 0) {
            return $res;
        }
        $hist = $this->listHistory();
        if (!$hist) {
            return true;
        }
        if ($this->eraseDups) {
            $hist = \array_flip(\array_flip($hist));
            \ksort($hist);
        }
        if ($this->historySize > 0) {
            $histsize = \count($hist);
            if ($histsize > $this->historySize) {
                $hist = \array_slice($hist, $histsize - $this->historySize);
            }
        }
        \readline_clear_history();
        foreach ($hist as $line) {
            \readline_add_history($line);
        }
        if ($this->historyFile !== false) {
            return \readline_write_history($this->historyFile);
        }
        return true;
    }
}
