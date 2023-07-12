<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Response;
interface ResponseCacheStrategyInterface
{
    public function add(Response $response);
    public function update(Response $response);
}
