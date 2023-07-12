<?php
namespace Psy\Readline;
use Hoa\Console\Readline\Readline as HoaReadline;
use Psy\Exception\BreakException;
class HoaConsole implements Readline
{
    private $hoaReadline;
    public static function isSupported()
    {
        return \class_exists('\Hoa\Console\Console', true);
    }
    public function __construct()
    {
        $this->hoaReadline = new HoaReadline();
    }
    public function addHistory($line)
    {
        $this->hoaReadline->addHistory($line);
        return true;
    }
    public function clearHistory()
    {
        $this->hoaReadline->clearHistory();
        return true;
    }
    public function listHistory()
    {
        $i = 0;
        $list = [];
        while (($item = $this->hoaReadline->getHistory($i++)) !== null) {
            $list[] = $item;
        }
        return $list;
    }
    public function readHistory()
    {
        return true;
    }
    public function readline($prompt = null)
    {
        return $this->hoaReadline->readLine($prompt);
    }
    public function redisplay()
    {
    }
    public function writeHistory()
    {
        return true;
    }
}
