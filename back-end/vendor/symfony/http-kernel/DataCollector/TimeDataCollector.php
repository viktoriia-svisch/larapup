<?php
namespace Symfony\Component\HttpKernel\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Stopwatch\Stopwatch;
class TimeDataCollector extends DataCollector implements LateDataCollectorInterface
{
    protected $kernel;
    protected $stopwatch;
    public function __construct(KernelInterface $kernel = null, Stopwatch $stopwatch = null)
    {
        $this->kernel = $kernel;
        $this->stopwatch = $stopwatch;
    }
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        if (null !== $this->kernel) {
            $startTime = $this->kernel->getStartTime();
        } else {
            $startTime = $request->server->get('REQUEST_TIME_FLOAT');
        }
        $this->data = [
            'token' => $response->headers->get('X-Debug-Token'),
            'start_time' => $startTime * 1000,
            'events' => [],
        ];
    }
    public function reset()
    {
        $this->data = [];
        if (null !== $this->stopwatch) {
            $this->stopwatch->reset();
        }
    }
    public function lateCollect()
    {
        if (null !== $this->stopwatch && isset($this->data['token'])) {
            $this->setEvents($this->stopwatch->getSectionEvents($this->data['token']));
        }
        unset($this->data['token']);
    }
    public function setEvents(array $events)
    {
        foreach ($events as $event) {
            $event->ensureStopped();
        }
        $this->data['events'] = $events;
    }
    public function getEvents()
    {
        return $this->data['events'];
    }
    public function getDuration()
    {
        if (!isset($this->data['events']['__section__'])) {
            return 0;
        }
        $lastEvent = $this->data['events']['__section__'];
        return $lastEvent->getOrigin() + $lastEvent->getDuration() - $this->getStartTime();
    }
    public function getInitTime()
    {
        if (!isset($this->data['events']['__section__'])) {
            return 0;
        }
        return $this->data['events']['__section__']->getOrigin() - $this->getStartTime();
    }
    public function getStartTime()
    {
        return $this->data['start_time'];
    }
    public function getName()
    {
        return 'time';
    }
}
