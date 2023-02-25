<?php
namespace Illuminate\Routing\Middleware;
use Closure;
use Illuminate\Redis\Limiters\DurationLimiter;
use Illuminate\Contracts\Redis\Factory as Redis;
class ThrottleRequestsWithRedis extends ThrottleRequests
{
    protected $redis;
    public $decaysAt;
    public $remaining;
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = $this->resolveMaxAttempts($request, $maxAttempts);
        if ($this->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            throw $this->buildException($key, $maxAttempts);
        }
        $response = $next($request);
        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }
    protected function tooManyAttempts($key, $maxAttempts, $decayMinutes)
    {
        $limiter = new DurationLimiter(
            $this->redis, $key, $maxAttempts, $decayMinutes * 60
        );
        return tap(! $limiter->acquire(), function () use ($limiter) {
            [$this->decaysAt, $this->remaining] = [
                $limiter->decaysAt, $limiter->remaining,
            ];
        });
    }
    protected function calculateRemainingAttempts($key, $maxAttempts, $retryAfter = null)
    {
        if (is_null($retryAfter)) {
            return $this->remaining;
        }
        return 0;
    }
    protected function getTimeUntilNextRetry($key)
    {
        return $this->decaysAt - $this->currentTime();
    }
}
