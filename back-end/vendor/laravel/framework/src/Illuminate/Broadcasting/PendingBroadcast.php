<?php
namespace Illuminate\Broadcasting;
use Illuminate\Contracts\Events\Dispatcher;
class PendingBroadcast
{
    protected $events;
    protected $event;
    public function __construct(Dispatcher $events, $event)
    {
        $this->event = $event;
        $this->events = $events;
    }
    public function toOthers()
    {
        if (method_exists($this->event, 'dontBroadcastToCurrentUser')) {
            $this->event->dontBroadcastToCurrentUser();
        }
        return $this;
    }
    public function __destruct()
    {
        $this->events->dispatch($this->event);
    }
}
