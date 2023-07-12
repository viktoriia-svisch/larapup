<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;
use Predis\Response\ErrorInterface;
use Symfony\Component\Cache\Traits\RedisClusterProxy;
use Symfony\Component\Cache\Traits\RedisProxy;
class RedisSessionHandler extends AbstractSessionHandler
{
    private $redis;
    private $prefix;
    public function __construct($redis, array $options = [])
    {
        if (
            !$redis instanceof \Redis &&
            !$redis instanceof \RedisArray &&
            !$redis instanceof \RedisCluster &&
            !$redis instanceof \Predis\Client &&
            !$redis instanceof RedisProxy &&
            !$redis instanceof RedisClusterProxy
        ) {
            throw new \InvalidArgumentException(sprintf('%s() expects parameter 1 to be Redis, RedisArray, RedisCluster or Predis\Client, %s given', __METHOD__, \is_object($redis) ? \get_class($redis) : \gettype($redis)));
        }
        if ($diff = array_diff(array_keys($options), ['prefix'])) {
            throw new \InvalidArgumentException(sprintf('The following options are not supported "%s"', implode(', ', $diff)));
        }
        $this->redis = $redis;
        $this->prefix = $options['prefix'] ?? 'sf_s';
    }
    protected function doRead($sessionId): string
    {
        return $this->redis->get($this->prefix.$sessionId) ?: '';
    }
    protected function doWrite($sessionId, $data): bool
    {
        $result = $this->redis->setEx($this->prefix.$sessionId, (int) ini_get('session.gc_maxlifetime'), $data);
        return $result && !$result instanceof ErrorInterface;
    }
    protected function doDestroy($sessionId): bool
    {
        $this->redis->del($this->prefix.$sessionId);
        return true;
    }
    public function close(): bool
    {
        return true;
    }
    public function gc($maxlifetime): bool
    {
        return true;
    }
    public function updateTimestamp($sessionId, $data)
    {
        return (bool) $this->redis->expire($this->prefix.$sessionId, (int) ini_get('session.gc_maxlifetime'));
    }
}
