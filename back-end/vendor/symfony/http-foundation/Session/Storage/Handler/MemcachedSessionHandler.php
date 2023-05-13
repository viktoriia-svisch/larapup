<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;
class MemcachedSessionHandler extends AbstractSessionHandler
{
    private $memcached;
    private $ttl;
    private $prefix;
    public function __construct(\Memcached $memcached, array $options = [])
    {
        $this->memcached = $memcached;
        if ($diff = array_diff(array_keys($options), ['prefix', 'expiretime'])) {
            throw new \InvalidArgumentException(sprintf('The following options are not supported "%s"', implode(', ', $diff)));
        }
        $this->ttl = isset($options['expiretime']) ? (int) $options['expiretime'] : 86400;
        $this->prefix = isset($options['prefix']) ? $options['prefix'] : 'sf2s';
    }
    public function close()
    {
        return $this->memcached->quit();
    }
    protected function doRead($sessionId)
    {
        return $this->memcached->get($this->prefix.$sessionId) ?: '';
    }
    public function updateTimestamp($sessionId, $data)
    {
        $this->memcached->touch($this->prefix.$sessionId, time() + $this->ttl);
        return true;
    }
    protected function doWrite($sessionId, $data)
    {
        return $this->memcached->set($this->prefix.$sessionId, $data, time() + $this->ttl);
    }
    protected function doDestroy($sessionId)
    {
        $result = $this->memcached->delete($this->prefix.$sessionId);
        return $result || \Memcached::RES_NOTFOUND == $this->memcached->getResultCode();
    }
    public function gc($maxlifetime)
    {
        return true;
    }
    protected function getMemcached()
    {
        return $this->memcached;
    }
}
