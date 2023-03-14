<?php
namespace Symfony\Component\HttpKernel\Tests\Fixtures;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
class TestEventDispatcher extends TraceableEventDispatcher
{
    public function getCalledListeners()
    {
        return ['foo'];
    }
    public function getNotCalledListeners()
    {
        return ['bar'];
    }
    public function reset()
    {
    }
    public function getOrphanedEvents()
    {
        return [];
    }
}
