<?php
namespace Psy\Readline;
use Psy\Util\Str;
class Libedit extends GNUReadline
{
    public static function isSupported()
    {
        return \function_exists('readline') && !\function_exists('readline_list_history');
    }
    public function listHistory()
    {
        $history = \file_get_contents($this->historyFile);
        if (!$history) {
            return [];
        }
        $history = \explode("\n", $history);
        if (\array_shift($history) !== '_HiStOrY_V2_') {
            return [];
        }
        $history = \array_map([$this, 'parseHistoryLine'], $history);
        return \array_values(\array_filter($history));
    }
    protected function parseHistoryLine($line)
    {
        if (!$line || $line[0] === "\0") {
            return;
        }
        if (($pos = \strpos($line, "\0")) !== false) {
            $line = \substr($line, 0, $pos);
        }
        return ($line !== '') ? Str::unvis($line) : null;
    }
}
