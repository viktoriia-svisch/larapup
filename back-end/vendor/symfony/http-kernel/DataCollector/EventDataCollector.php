<?php
namespace Symfony\Component\HttpKernel\DataCollector;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\ResetInterface;
class EventDataCollector extends DataCollector implements LateDataCollectorInterface
{
    protected $dispatcher;
    public function __construct(EventDispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'called_listeners' => [],
            'not_called_listeners' => [],
            'orphaned_events' => [],
        ];
    }
    public function reset()
    {
        $this->data = [];
        if ($this->dispatcher instanceof ResetInterface) {
            $this->dispatcher->reset();
        }
    }
    public function lateCollect()
    {
        if ($this->dispatcher instanceof TraceableEventDispatcherInterface) {
            $this->setCalledListeners($this->dispatcher->getCalledListeners());
            $this->setNotCalledListeners($this->dispatcher->getNotCalledListeners());
        }
        if ($this->dispatcher instanceof TraceableEventDispatcher) {
            $this->setOrphanedEvents($this->dispatcher->getOrphanedEvents());
        }
        $this->data = $this->cloneVar($this->data);
    }
    public function setCalledListeners(array $listeners)
    {
        $this->data['called_listeners'] = $listeners;
    }
    public function getCalledListeners()
    {
        return $this->data['called_listeners'];
    }
    public function setNotCalledListeners(array $listeners)
    {
        $this->data['not_called_listeners'] = $listeners;
    }
    public function getNotCalledListeners()
    {
        return $this->data['not_called_listeners'];
    }
    public function setOrphanedEvents(array $events)
    {
        $this->data['orphaned_events'] = $events;
    }
    public function getOrphanedEvents()
    {
        return $this->data['orphaned_events'];
    }
    public function getName()
    {
        return 'events';
    }
}
