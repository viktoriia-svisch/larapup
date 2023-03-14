<?php
namespace Symfony\Component\HttpKernel\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class MemoryDataCollector extends DataCollector implements LateDataCollectorInterface
{
    public function __construct()
    {
        $this->reset();
    }
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->updateMemoryUsage();
    }
    public function reset()
    {
        $this->data = [
            'memory' => 0,
            'memory_limit' => $this->convertToBytes(ini_get('memory_limit')),
        ];
    }
    public function lateCollect()
    {
        $this->updateMemoryUsage();
    }
    public function getMemory()
    {
        return $this->data['memory'];
    }
    public function getMemoryLimit()
    {
        return $this->data['memory_limit'];
    }
    public function updateMemoryUsage()
    {
        $this->data['memory'] = memory_get_peak_usage(true);
    }
    public function getName()
    {
        return 'memory';
    }
    private function convertToBytes($memoryLimit)
    {
        if ('-1' === $memoryLimit) {
            return -1;
        }
        $memoryLimit = strtolower($memoryLimit);
        $max = strtolower(ltrim($memoryLimit, '+'));
        if (0 === strpos($max, '0x')) {
            $max = \intval($max, 16);
        } elseif (0 === strpos($max, '0')) {
            $max = \intval($max, 8);
        } else {
            $max = (int) $max;
        }
        switch (substr($memoryLimit, -1)) {
            case 't': $max *= 1024;
            case 'g': $max *= 1024;
            case 'm': $max *= 1024;
            case 'k': $max *= 1024;
        }
        return $max;
    }
}
