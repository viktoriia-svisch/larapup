<?php
namespace Illuminate\Broadcasting;
use ReflectionClass;
use ReflectionProperty;
use Illuminate\Support\Arr;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Broadcasting\Broadcaster;
class BroadcastEvent implements ShouldQueue
{
    use Queueable;
    public $event;
    public function __construct($event)
    {
        $this->event = $event;
    }
    public function handle(Broadcaster $broadcaster)
    {
        $name = method_exists($this->event, 'broadcastAs')
                ? $this->event->broadcastAs() : get_class($this->event);
        $broadcaster->broadcast(
            Arr::wrap($this->event->broadcastOn()), $name,
            $this->getPayloadFromEvent($this->event)
        );
    }
    protected function getPayloadFromEvent($event)
    {
        if (method_exists($event, 'broadcastWith')) {
            return array_merge(
                $event->broadcastWith(), ['socket' => data_get($event, 'socket')]
            );
        }
        $payload = [];
        foreach ((new ReflectionClass($event))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $payload[$property->getName()] = $this->formatProperty($property->getValue($event));
        }
        unset($payload['broadcastQueue']);
        return $payload;
    }
    protected function formatProperty($value)
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }
        return $value;
    }
    public function displayName()
    {
        return get_class($this->event);
    }
    public function __clone()
    {
        $this->event = clone $this->event;
    }
}
