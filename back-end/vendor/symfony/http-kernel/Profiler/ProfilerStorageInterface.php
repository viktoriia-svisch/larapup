<?php
namespace Symfony\Component\HttpKernel\Profiler;
interface ProfilerStorageInterface
{
    public function find($ip, $url, $limit, $method, $start = null, $end = null);
    public function read($token);
    public function write(Profile $profile);
    public function purge();
}
