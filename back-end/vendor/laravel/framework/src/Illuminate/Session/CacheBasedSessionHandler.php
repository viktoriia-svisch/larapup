<?php
namespace Illuminate\Session;
use SessionHandlerInterface;
use Illuminate\Contracts\Cache\Repository as CacheContract;
class CacheBasedSessionHandler implements SessionHandlerInterface
{
    protected $cache;
    protected $minutes;
    public function __construct(CacheContract $cache, $minutes)
    {
        $this->cache = $cache;
        $this->minutes = $minutes;
    }
    public function open($savePath, $sessionName)
    {
        return true;
    }
    public function close()
    {
        return true;
    }
    public function read($sessionId)
    {
        return $this->cache->get($sessionId, '');
    }
    public function write($sessionId, $data)
    {
        return $this->cache->put($sessionId, $data, $this->minutes);
    }
    public function destroy($sessionId)
    {
        return $this->cache->forget($sessionId);
    }
    public function gc($lifetime)
    {
        return true;
    }
    public function getCache()
    {
        return $this->cache;
    }
}
