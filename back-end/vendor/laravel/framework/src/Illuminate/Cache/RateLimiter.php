<?php
namespace Illuminate\Cache;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Contracts\Cache\Repository as Cache;
class RateLimiter
{
    use InteractsWithTime;
    protected $cache;
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
    public function tooManyAttempts($key, $maxAttempts)
    {
        if ($this->attempts($key) >= $maxAttempts) {
            if ($this->cache->has($key.':timer')) {
                return true;
            }
            $this->resetAttempts($key);
        }
        return false;
    }
    public function hit($key, $decayMinutes = 1)
    {
        $this->cache->add(
            $key.':timer', $this->availableAt($decayMinutes * 60), $decayMinutes
        );
        $added = $this->cache->add($key, 0, $decayMinutes);
        $hits = (int) $this->cache->increment($key);
        if (! $added && $hits == 1) {
            $this->cache->put($key, 1, $decayMinutes);
        }
        return $hits;
    }
    public function attempts($key)
    {
        return $this->cache->get($key, 0);
    }
    public function resetAttempts($key)
    {
        return $this->cache->forget($key);
    }
    public function retriesLeft($key, $maxAttempts)
    {
        $attempts = $this->attempts($key);
        return $maxAttempts - $attempts;
    }
    public function clear($key)
    {
        $this->resetAttempts($key);
        $this->cache->forget($key.':timer');
    }
    public function availableIn($key)
    {
        return $this->cache->get($key.':timer') - $this->currentTime();
    }
}
