<?php
namespace Illuminate\Queue\Failed;
class NullFailedJobProvider implements FailedJobProviderInterface
{
    public function log($connection, $queue, $payload, $exception)
    {
    }
    public function all()
    {
        return [];
    }
    public function find($id)
    {
    }
    public function forget($id)
    {
        return true;
    }
    public function flush()
    {
    }
}
