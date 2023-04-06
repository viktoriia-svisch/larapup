<?php
namespace Symfony\Component\Debug;
use Psr\Log\AbstractLogger;
class BufferingLogger extends AbstractLogger
{
    private $logs = [];
    public function log($level, $message, array $context = [])
    {
        $this->logs[] = [$level, $message, $context];
    }
    public function cleanLogs()
    {
        $logs = $this->logs;
        $this->logs = [];
        return $logs;
    }
}
