<?php
namespace Illuminate\Queue\Events;
class JobProcessing
{
    public $connectionName;
    public $job;
    public function __construct($connectionName, $job)
    {
        $this->job = $job;
        $this->connectionName = $connectionName;
    }
}
