<?php
namespace Illuminate\Queue;
class ListenerOptions extends WorkerOptions
{
    public $environment;
    public function __construct($environment = null, $delay = 0, $memory = 128, $timeout = 60, $sleep = 3, $maxTries = 0, $force = false)
    {
        $this->environment = $environment;
        parent::__construct($delay, $memory, $timeout, $sleep, $maxTries, $force);
    }
}
