<?php
namespace Illuminate\Queue\Connectors;
use Pheanstalk\Connection;
use Pheanstalk\Pheanstalk;
use Illuminate\Queue\BeanstalkdQueue;
use Pheanstalk\Contract\PheanstalkInterface;
class BeanstalkdConnector implements ConnectorInterface
{
    public function connect(array $config)
    {
        $retryAfter = $config['retry_after'] ?? Pheanstalk::DEFAULT_TTR;
        return new BeanstalkdQueue($this->pheanstalk($config), $config['queue'], $retryAfter);
    }
    protected function pheanstalk(array $config)
    {
        if (interface_exists(PheanstalkInterface::class)) {
            return Pheanstalk::create(
                $config['host'],
                $config['port'] ?? Pheanstalk::DEFAULT_PORT,
                $config['timeout'] ?? Connection::DEFAULT_CONNECT_TIMEOUT
            );
        }
        return new Pheanstalk(
            $config['host'],
            $config['port'] ?? Pheanstalk::DEFAULT_PORT,
            $config['timeout'] ?? Connection::DEFAULT_CONNECT_TIMEOUT,
            $config['persistent'] ?? false
        );
    }
}
