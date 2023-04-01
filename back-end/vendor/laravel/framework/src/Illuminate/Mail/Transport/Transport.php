<?php
namespace Illuminate\Mail\Transport;
use Swift_Transport;
use Swift_Events_SendEvent;
use Swift_Mime_SimpleMessage;
use Swift_Events_EventListener;
abstract class Transport implements Swift_Transport
{
    public $plugins = [];
    public function isStarted()
    {
        return true;
    }
    public function start()
    {
        return true;
    }
    public function stop()
    {
        return true;
    }
    public function ping()
    {
        return true;
    }
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        array_push($this->plugins, $plugin);
    }
    protected function beforeSendPerformed(Swift_Mime_SimpleMessage $message)
    {
        $event = new Swift_Events_SendEvent($this, $message);
        foreach ($this->plugins as $plugin) {
            if (method_exists($plugin, 'beforeSendPerformed')) {
                $plugin->beforeSendPerformed($event);
            }
        }
    }
    protected function sendPerformed(Swift_Mime_SimpleMessage $message)
    {
        $event = new Swift_Events_SendEvent($this, $message);
        foreach ($this->plugins as $plugin) {
            if (method_exists($plugin, 'sendPerformed')) {
                $plugin->sendPerformed($event);
            }
        }
    }
    protected function numberOfRecipients(Swift_Mime_SimpleMessage $message)
    {
        return count(array_merge(
            (array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc()
        ));
    }
}
