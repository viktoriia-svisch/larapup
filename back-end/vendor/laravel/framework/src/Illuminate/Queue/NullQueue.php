<?php
namespace Illuminate\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
class NullQueue extends Queue implements QueueContract
{
    public function size($queue = null)
    {
        return 0;
    }
    public function push($job, $data = '', $queue = null)
    {
    }
    public function pushRaw($payload, $queue = null, array $options = [])
    {
    }
    public function later($delay, $job, $data = '', $queue = null)
    {
    }
    public function pop($queue = null)
    {
    }
}
