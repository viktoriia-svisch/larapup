<?php
namespace Illuminate\Cache;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Contracts\Cache\Lock as LockContract;
use Illuminate\Contracts\Cache\LockTimeoutException;
abstract class Lock implements LockContract
{
    use InteractsWithTime;
    protected $name;
    protected $seconds;
    public function __construct($name, $seconds)
    {
        $this->name = $name;
        $this->seconds = $seconds;
    }
    abstract public function acquire();
    abstract public function release();
    public function get($callback = null)
    {
        $result = $this->acquire();
        if ($result && is_callable($callback)) {
            return tap($callback(), function () {
                $this->release();
            });
        }
        return $result;
    }
    public function block($seconds, $callback = null)
    {
        $starting = $this->currentTime();
        while (! $this->acquire()) {
            usleep(250 * 1000);
            if ($this->currentTime() - $seconds >= $starting) {
                throw new LockTimeoutException;
            }
        }
        if (is_callable($callback)) {
            return tap($callback(), function () {
                $this->release();
            });
        }
        return true;
    }
}
