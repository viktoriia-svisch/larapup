<?php
namespace Illuminate\Cache;
use Illuminate\Contracts\Cache\Store;
class ApcStore extends TaggableStore implements Store
{
    use RetrievesMultipleKeys;
    protected $apc;
    protected $prefix;
    public function __construct(ApcWrapper $apc, $prefix = '')
    {
        $this->apc = $apc;
        $this->prefix = $prefix;
    }
    public function get($key)
    {
        $value = $this->apc->get($this->prefix.$key);
        if ($value !== false) {
            return $value;
        }
    }
    public function put($key, $value, $minutes)
    {
        $this->apc->put($this->prefix.$key, $value, (int) ($minutes * 60));
    }
    public function increment($key, $value = 1)
    {
        return $this->apc->increment($this->prefix.$key, $value);
    }
    public function decrement($key, $value = 1)
    {
        return $this->apc->decrement($this->prefix.$key, $value);
    }
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }
    public function forget($key)
    {
        return $this->apc->delete($this->prefix.$key);
    }
    public function flush()
    {
        return $this->apc->flush();
    }
    public function getPrefix()
    {
        return $this->prefix;
    }
}
