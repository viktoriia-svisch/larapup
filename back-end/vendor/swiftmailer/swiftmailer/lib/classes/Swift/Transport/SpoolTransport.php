<?php
class Swift_Transport_SpoolTransport implements Swift_Transport
{
    private $spool;
    private $eventDispatcher;
    public function __construct(Swift_Events_EventDispatcher $eventDispatcher, Swift_Spool $spool = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->spool = $spool;
    }
    public function setSpool(Swift_Spool $spool)
    {
        $this->spool = $spool;
        return $this;
    }
    public function getSpool()
    {
        return $this->spool;
    }
    public function isStarted()
    {
        return true;
    }
    public function start()
    {
    }
    public function stop()
    {
    }
    public function ping()
    {
        return true;
    }
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        if ($evt = $this->eventDispatcher->createSendEvent($this, $message)) {
            $this->eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }
        $success = $this->spool->queueMessage($message);
        if ($evt) {
            $evt->setResult($success ? Swift_Events_SendEvent::RESULT_SPOOLED : Swift_Events_SendEvent::RESULT_FAILED);
            $this->eventDispatcher->dispatchEvent($evt, 'sendPerformed');
        }
        return 1;
    }
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->eventDispatcher->bindEventListener($plugin);
    }
}
