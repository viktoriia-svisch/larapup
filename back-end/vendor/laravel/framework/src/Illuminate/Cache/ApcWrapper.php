<?php
namespace Illuminate\Cache;
class ApcWrapper
{
    protected $apcu = false;
    public function __construct()
    {
        $this->apcu = function_exists('apcu_fetch');
    }
    public function get($key)
    {
        return $this->apcu ? apcu_fetch($key) : apc_fetch($key);
    }
    public function put($key, $value, $seconds)
    {
        return $this->apcu ? apcu_store($key, $value, $seconds) : apc_store($key, $value, $seconds);
    }
    public function increment($key, $value)
    {
        return $this->apcu ? apcu_inc($key, $value) : apc_inc($key, $value);
    }
    public function decrement($key, $value)
    {
        return $this->apcu ? apcu_dec($key, $value) : apc_dec($key, $value);
    }
    public function delete($key)
    {
        return $this->apcu ? apcu_delete($key) : apc_delete($key);
    }
    public function flush()
    {
        return $this->apcu ? apcu_clear_cache() : apc_clear_cache('user');
    }
}
