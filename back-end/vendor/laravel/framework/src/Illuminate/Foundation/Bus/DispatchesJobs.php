<?php
namespace Illuminate\Foundation\Bus;
use Illuminate\Contracts\Bus\Dispatcher;
trait DispatchesJobs
{
    protected function dispatch($job)
    {
        return app(Dispatcher::class)->dispatch($job);
    }
    public function dispatchNow($job)
    {
        return app(Dispatcher::class)->dispatchNow($job);
    }
}
