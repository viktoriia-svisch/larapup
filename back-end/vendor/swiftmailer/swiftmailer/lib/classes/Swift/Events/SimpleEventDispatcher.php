<?php
class Swift_Events_SimpleEventDispatcher implements Swift_Events_EventDispatcher
{
    private $eventMap = [];
    private $listeners = [];
    private $bubbleQueue = [];
    public function __construct()
    {
        $this->eventMap = [
            'Swift_Events_CommandEvent' => 'Swift_Events_CommandListener',
            'Swift_Events_ResponseEvent' => 'Swift_Events_ResponseListener',
            'Swift_Events_SendEvent' => 'Swift_Events_SendListener',
            'Swift_Events_TransportChangeEvent' => 'Swift_Events_TransportChangeListener',
            'Swift_Events_TransportExceptionEvent' => 'Swift_Events_TransportExceptionListener',
            ];
    }
    public function createSendEvent(Swift_Transport $source, Swift_Mime_SimpleMessage $message)
    {
        return new Swift_Events_SendEvent($source, $message);
    }
    public function createCommandEvent(Swift_Transport $source, $command, $successCodes = [])
    {
        return new Swift_Events_CommandEvent($source, $command, $successCodes);
    }
    public function createResponseEvent(Swift_Transport $source, $response, $valid)
    {
        return new Swift_Events_ResponseEvent($source, $response, $valid);
    }
    public function createTransportChangeEvent(Swift_Transport $source)
    {
        return new Swift_Events_TransportChangeEvent($source);
    }
    public function createTransportExceptionEvent(Swift_Transport $source, Swift_TransportException $ex)
    {
        return new Swift_Events_TransportExceptionEvent($source, $ex);
    }
    public function bindEventListener(Swift_Events_EventListener $listener)
    {
        foreach ($this->listeners as $l) {
            if ($l === $listener) {
                return;
            }
        }
        $this->listeners[] = $listener;
    }
    public function dispatchEvent(Swift_Events_EventObject $evt, $target)
    {
        $this->prepareBubbleQueue($evt);
        $this->bubble($evt, $target);
    }
    private function prepareBubbleQueue(Swift_Events_EventObject $evt)
    {
        $this->bubbleQueue = [];
        $evtClass = get_class($evt);
        foreach ($this->listeners as $listener) {
            if (array_key_exists($evtClass, $this->eventMap)
                && ($listener instanceof $this->eventMap[$evtClass])) {
                $this->bubbleQueue[] = $listener;
            }
        }
    }
    private function bubble(Swift_Events_EventObject $evt, $target)
    {
        if (!$evt->bubbleCancelled() && $listener = array_shift($this->bubbleQueue)) {
            $listener->$target($evt);
            $this->bubble($evt, $target);
        }
    }
}
