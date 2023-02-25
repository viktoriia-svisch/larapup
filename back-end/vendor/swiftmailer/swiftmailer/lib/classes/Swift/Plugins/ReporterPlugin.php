<?php
class Swift_Plugins_ReporterPlugin implements Swift_Events_SendListener
{
    private $reporter;
    public function __construct(Swift_Plugins_Reporter $reporter)
    {
        $this->reporter = $reporter;
    }
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
    }
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();
        $failures = array_flip($evt->getFailedRecipients());
        foreach ((array) $message->getTo() as $address => $null) {
            $this->reporter->notify($message, $address, (array_key_exists($address, $failures) ? Swift_Plugins_Reporter::RESULT_FAIL : Swift_Plugins_Reporter::RESULT_PASS));
        }
        foreach ((array) $message->getCc() as $address => $null) {
            $this->reporter->notify($message, $address, (array_key_exists($address, $failures) ? Swift_Plugins_Reporter::RESULT_FAIL : Swift_Plugins_Reporter::RESULT_PASS));
        }
        foreach ((array) $message->getBcc() as $address => $null) {
            $this->reporter->notify($message, $address, (array_key_exists($address, $failures) ? Swift_Plugins_Reporter::RESULT_FAIL : Swift_Plugins_Reporter::RESULT_PASS));
        }
    }
}
