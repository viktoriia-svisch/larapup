<?php
namespace Tymon\JWTAuth\Providers\Storage;
use BadMethodCallException;
use Tymon\JWTAuth\Contracts\Providers\Storage;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Illuminate\Contracts\Cache\Repository as CacheContract;
class Illuminate implements Storage
{
    protected $cache;
    protected $tag = 'tymon.jwt';
    protected $supportsTags;
    public function __construct(CacheContract $cache)
    {
        $this->cache = $cache;
    }
    public function add($key, $value, $minutes)
    {
        $this->cache()->put($key, $value, $minutes);
    }
    public function forever($key, $value)
    {
        $this->cache()->forever($key, $value);
    }
    public function get($key)
    {
        return $this->cache()->get($key);
    }
    public function destroy($key)
    {
        return $this->cache()->forget($key);
    }
    public function flush()
    {
        $this->cache()->flush();
    }
    protected function cache()
    {
        if ($this->supportsTags === null) {
            $this->determineTagSupport();
        }
        if ($this->supportsTags) {
            return $this->cache->tags($this->tag);
        }
        return $this->cache;
    }
    protected function determineTagSupport()
    {
        if (method_exists($this->cache, 'tags') || $this->cache instanceof PsrCacheInterface) {
            try {
                $this->cache->tags($this->tag);
                $this->supportsTags = true;
            } catch (BadMethodCallException $ex) {
                $this->supportsTags = false;
            }
        } else {
            if (method_exists($this->cache, 'getStore')) {
                $this->supportsTags = method_exists($this->cache->getStore(), 'tags');
            } else {
                $this->supportsTags = false;
            }
        }
    }
}
