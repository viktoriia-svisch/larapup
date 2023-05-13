<?php
namespace Illuminate\Queue;
class WorkerOptions
{
    public $delay;
    public $memory;
    public $timeout;
    public $sleep;
    public $maxTries;
    public $force;
    public $stopWhenEmpty;
    public function __construct($delay = 0, $memory = 128, $timeout = 60, $sleep = 3, $maxTries = 0, $force = false, $stopWhenEmpty = false)
    {
        $this->delay = $delay;
        $this->sleep = $sleep;
        $this->force = $force;
        $this->memory = $memory;
        $this->timeout = $timeout;
        $this->maxTries = $maxTries;
        $this->stopWhenEmpty = $stopWhenEmpty;
    }
}
