<?php
namespace Illuminate\Queue\Events;
class WorkerStopping
{
    public $status;
    public function __construct($status = 0)
    {
        $this->status = $status;
    }
}
