<?php
namespace Illuminate\Cache;
use Illuminate\Contracts\Cache\Store;
class NullStore extends TaggableStore implements Store
{
    use RetrievesMultipleKeys;
    protected $storage = [];
    public function get($key)
    {
    }
    public function put($key, $value, $minutes)
    {
    }
    public function increment($key, $value = 1)
    {
    }
    public function decrement($key, $value = 1)
    {
    }
    public function forever($key, $value)
    {
    }
    public function forget($key)
    {
    }
    public function flush()
    {
        return true;
    }
    public function getPrefix()
    {
        return '';
    }
}
