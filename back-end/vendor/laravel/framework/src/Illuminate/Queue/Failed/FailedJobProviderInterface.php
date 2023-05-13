<?php
namespace Illuminate\Queue\Failed;
interface FailedJobProviderInterface
{
    public function log($connection, $queue, $payload, $exception);
    public function all();
    public function find($id);
    public function forget($id);
    public function flush();
}
