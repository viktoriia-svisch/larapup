<?php
class Swift_Transport_LoadBalancedTransport implements Swift_Transport
{
    private $deadTransports = [];
    protected $transports = [];
    protected $lastUsedTransport = null;
    public function __construct()
    {
    }
    public function setTransports(array $transports)
    {
        $this->transports = $transports;
        $this->deadTransports = [];
    }
    public function getTransports()
    {
        return array_merge($this->transports, $this->deadTransports);
    }
    public function getLastUsedTransport()
    {
        return $this->lastUsedTransport;
    }
    public function isStarted()
    {
        return count($this->transports) > 0;
    }
    public function start()
    {
        $this->transports = array_merge($this->transports, $this->deadTransports);
    }
    public function stop()
    {
        foreach ($this->transports as $transport) {
            $transport->stop();
        }
    }
    public function ping()
    {
        foreach ($this->transports as $transport) {
            if (!$transport->ping()) {
                $this->killCurrentTransport();
            }
        }
        return count($this->transports) > 0;
    }
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $maxTransports = count($this->transports);
        $sent = 0;
        $this->lastUsedTransport = null;
        for ($i = 0; $i < $maxTransports
            && $transport = $this->getNextTransport(); ++$i) {
            try {
                if (!$transport->isStarted()) {
                    $transport->start();
                }
                if ($sent = $transport->send($message, $failedRecipients)) {
                    $this->lastUsedTransport = $transport;
                    break;
                }
            } catch (Swift_TransportException $e) {
                $this->killCurrentTransport();
            }
        }
        if (0 == count($this->transports)) {
            throw new Swift_TransportException(
                'All Transports in LoadBalancedTransport failed, or no Transports available'
                );
        }
        return $sent;
    }
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        foreach ($this->transports as $transport) {
            $transport->registerPlugin($plugin);
        }
    }
    protected function getNextTransport()
    {
        if ($next = array_shift($this->transports)) {
            $this->transports[] = $next;
        }
        return $next;
    }
    protected function killCurrentTransport()
    {
        if ($transport = array_pop($this->transports)) {
            try {
                $transport->stop();
            } catch (Exception $e) {
            }
            $this->deadTransports[] = $transport;
        }
    }
}
