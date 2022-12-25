<?php
namespace Illuminate\Cache;
class RedisLock extends Lock
{
    protected $redis;
    public function __construct($redis, $name, $seconds)
    {
        parent::__construct($name, $seconds);
        $this->redis = $redis;
    }
    public function acquire()
    {
        $result = $this->redis->setnx($this->name, 1);
        if ($result === 1 && $this->seconds > 0) {
            $this->redis->expire($this->name, $this->seconds);
        }
        return $result === 1;
    }
    public function release()
    {
        $this->redis->del($this->name);
    }
}
