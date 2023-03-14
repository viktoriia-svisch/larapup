<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
interface StoreInterface
{
    public function lookup(Request $request);
    public function write(Request $request, Response $response);
    public function invalidate(Request $request);
    public function lock(Request $request);
    public function unlock(Request $request);
    public function isLocked(Request $request);
    public function purge($url);
    public function cleanup();
}
