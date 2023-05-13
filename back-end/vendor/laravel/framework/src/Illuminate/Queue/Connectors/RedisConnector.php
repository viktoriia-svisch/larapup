<?php
namespace Illuminate\Queue\Connectors;
use Illuminate\Queue\RedisQueue;
use Illuminate\Contracts\Redis\Factory as Redis;
class RedisConnector implements ConnectorInterface
{
    protected $redis;
    protected $connection;
    public function __construct(Redis $redis, $connection = null)
    {
        $this->redis = $redis;
        $this->connection = $connection;
    }
    public function connect(array $config)
    {
        return new RedisQueue(
            $this->redis, $config['queue'],
            $config['connection'] ?? $this->connection,
            $config['retry_after'] ?? 60,
            $config['block_for'] ?? null
        );
    }
}
