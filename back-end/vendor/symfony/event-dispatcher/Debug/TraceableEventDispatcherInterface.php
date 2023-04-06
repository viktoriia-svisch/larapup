<?php
namespace Symfony\Component\EventDispatcher\Debug;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ResetInterface;
interface TraceableEventDispatcherInterface extends EventDispatcherInterface, ResetInterface
{
    public function getCalledListeners();
    public function getNotCalledListeners();
}
