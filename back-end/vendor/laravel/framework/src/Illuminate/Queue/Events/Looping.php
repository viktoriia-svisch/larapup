<?php
namespace Illuminate\Queue\Events;
class Looping
{
    public $connectionName;
    public $queue;
    public function __construct($connectionName, $queue)
    {
        $this->queue = $queue;
        $this->connectionName = $connectionName;
    }
}
