<?php
namespace Illuminate\Cache;
use Illuminate\Contracts\Cache\Store;
class ArrayStore extends TaggableStore implements Store
{
    use RetrievesMultipleKeys;
    protected $storage = [];
    public function get($key)
    {
        return $this->storage[$key] ?? null;
    }
    public function put($key, $value, $minutes)
    {
        $this->storage[$key] = $value;
    }
    public function increment($key, $value = 1)
    {
        $this->storage[$key] = ! isset($this->storage[$key])
                ? $value : ((int) $this->storage[$key]) + $value;
        return $this->storage[$key];
    }
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }
    public function forget($key)
    {
        unset($this->storage[$key]);
        return true;
    }
    public function flush()
    {
        $this->storage = [];
        return true;
    }
    public function getPrefix()
    {
        return '';
    }
}
