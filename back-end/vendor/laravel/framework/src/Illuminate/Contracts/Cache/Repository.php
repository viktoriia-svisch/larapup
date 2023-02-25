<?php
namespace Illuminate\Contracts\Cache;
use Closure;
use Psr\SimpleCache\CacheInterface;
interface Repository extends CacheInterface
{
    public function has($key);
    public function get($key, $default = null);
    public function pull($key, $default = null);
    public function put($key, $value, $minutes);
    public function add($key, $value, $minutes);
    public function increment($key, $value = 1);
    public function decrement($key, $value = 1);
    public function forever($key, $value);
    public function remember($key, $minutes, Closure $callback);
    public function sear($key, Closure $callback);
    public function rememberForever($key, Closure $callback);
    public function forget($key);
    public function getStore();
}
