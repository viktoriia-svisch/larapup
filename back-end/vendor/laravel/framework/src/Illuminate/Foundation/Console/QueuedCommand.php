<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Console\Kernel as KernelContract;
class QueuedCommand implements ShouldQueue
{
    use Dispatchable, Queueable;
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function handle(KernelContract $kernel)
    {
        call_user_func_array([$kernel, 'call'], $this->data);
    }
}
