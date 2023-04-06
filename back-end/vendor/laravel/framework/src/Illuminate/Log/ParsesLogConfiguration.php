<?php
namespace Illuminate\Log;
use InvalidArgumentException;
use Monolog\Logger as Monolog;
trait ParsesLogConfiguration
{
    protected $levels = [
        'debug' => Monolog::DEBUG,
        'info' => Monolog::INFO,
        'notice' => Monolog::NOTICE,
        'warning' => Monolog::WARNING,
        'error' => Monolog::ERROR,
        'critical' => Monolog::CRITICAL,
        'alert' => Monolog::ALERT,
        'emergency' => Monolog::EMERGENCY,
    ];
    abstract protected function getFallbackChannelName();
    protected function level(array $config)
    {
        $level = $config['level'] ?? 'debug';
        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }
        throw new InvalidArgumentException('Invalid log level.');
    }
    protected function parseChannel(array $config)
    {
        if (! isset($config['name'])) {
            return $this->getFallbackChannelName();
        }
        return $config['name'];
    }
}
