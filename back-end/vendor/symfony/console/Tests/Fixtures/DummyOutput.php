<?php
namespace Symfony\Component\Console\Tests\Fixtures;
use Symfony\Component\Console\Output\BufferedOutput;
class DummyOutput extends BufferedOutput
{
    public function getLogs()
    {
        $logs = [];
        foreach (explode(PHP_EOL, trim($this->fetch())) as $message) {
            preg_match('/^\[(.*)\] (.*)/', $message, $matches);
            $logs[] = sprintf('%s %s', $matches[1], $matches[2]);
        }
        return $logs;
    }
}
