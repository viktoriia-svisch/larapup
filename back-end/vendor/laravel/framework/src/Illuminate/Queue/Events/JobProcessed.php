<?php
namespace Illuminate\Queue\Events;
class JobProcessed
{
    public $connectionName;
    public $job;
    public function __construct($connectionName, $job)
    {
        $this->job = $job;
        $this->connectionName = $connectionName;
    }
}
