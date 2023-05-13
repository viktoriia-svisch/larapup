<?php
namespace Illuminate\Cache;
class MemcachedLock extends Lock
{
    protected $memcached;
    public function __construct($memcached, $name, $seconds)
    {
        parent::__construct($name, $seconds);
        $this->memcached = $memcached;
    }
    public function acquire()
    {
        return $this->memcached->add(
            $this->name, 1, $this->seconds
        );
    }
    public function release()
    {
        $this->memcached->delete($this->name);
    }
}
