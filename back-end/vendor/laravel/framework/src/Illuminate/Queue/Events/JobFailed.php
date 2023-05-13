<?php
namespace Illuminate\Queue\Events;
class JobFailed
{
    public $connectionName;
    public $job;
    public $exception;
    public function __construct($connectionName, $job, $exception)
    {
        $this->job = $job;
        $this->exception = $exception;
        $this->connectionName = $connectionName;
    }
}
