<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
interface SurrogateInterface
{
    public function getName();
    public function createCacheStrategy();
    public function hasSurrogateCapability(Request $request);
    public function addSurrogateCapability(Request $request);
    public function addSurrogateControl(Response $response);
    public function needsParsing(Response $response);
    public function renderIncludeTag($uri, $alt = null, $ignoreErrors = true, $comment = '');
    public function process(Request $request, Response $response);
    public function handle(HttpCache $cache, $uri, $alt, $ignoreErrors);
}
