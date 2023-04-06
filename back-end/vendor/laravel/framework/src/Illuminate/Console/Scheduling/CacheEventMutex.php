<?php
namespace Illuminate\Console\Scheduling;
use Illuminate\Contracts\Cache\Factory as Cache;
class CacheEventMutex implements EventMutex
{
    public $cache;
    public $store;
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
    public function create(Event $event)
    {
        return $this->cache->store($this->store)->add(
            $event->mutexName(), true, $event->expiresAt
        );
    }
    public function exists(Event $event)
    {
        return $this->cache->store($this->store)->has($event->mutexName());
    }
    public function forget(Event $event)
    {
        $this->cache->store($this->store)->forget($event->mutexName());
    }
    public function useStore($store)
    {
        $this->store = $store;
        return $this;
    }
}
