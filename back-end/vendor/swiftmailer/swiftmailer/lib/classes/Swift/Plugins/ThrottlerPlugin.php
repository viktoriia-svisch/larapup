<?php
class Swift_Plugins_ThrottlerPlugin extends Swift_Plugins_BandwidthMonitorPlugin implements Swift_Plugins_Sleeper, Swift_Plugins_Timer
{
    const BYTES_PER_MINUTE = 0x01;
    const MESSAGES_PER_SECOND = 0x11;
    const MESSAGES_PER_MINUTE = 0x10;
    private $sleeper;
    private $timer;
    private $start;
    private $rate;
    private $mode;
    private $messages = 0;
    public function __construct($rate, $mode = self::BYTES_PER_MINUTE, Swift_Plugins_Sleeper $sleeper = null, Swift_Plugins_Timer $timer = null)
    {
        $this->rate = $rate;
        $this->mode = $mode;
        $this->sleeper = $sleeper;
        $this->timer = $timer;
    }
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $time = $this->getTimestamp();
        if (!isset($this->start)) {
            $this->start = $time;
        }
        $duration = $time - $this->start;
        switch ($this->mode) {
            case self::BYTES_PER_MINUTE:
                $sleep = $this->throttleBytesPerMinute($duration);
                break;
            case self::MESSAGES_PER_SECOND:
                $sleep = $this->throttleMessagesPerSecond($duration);
                break;
            case self::MESSAGES_PER_MINUTE:
                $sleep = $this->throttleMessagesPerMinute($duration);
                break;
            default:
                $sleep = 0;
                break;
        }
        if ($sleep > 0) {
            $this->sleep($sleep);
        }
    }
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        parent::sendPerformed($evt);
        ++$this->messages;
    }
    public function sleep($seconds)
    {
        if (isset($this->sleeper)) {
            $this->sleeper->sleep($seconds);
        } else {
            sleep($seconds);
        }
    }
    public function getTimestamp()
    {
        if (isset($this->timer)) {
            return $this->timer->getTimestamp();
        }
        return time();
    }
    private function throttleBytesPerMinute($timePassed)
    {
        $expectedDuration = $this->getBytesOut() / ($this->rate / 60);
        return (int) ceil($expectedDuration - $timePassed);
    }
    private function throttleMessagesPerSecond($timePassed)
    {
        $expectedDuration = $this->messages / $this->rate;
        return (int) ceil($expectedDuration - $timePassed);
    }
    private function throttleMessagesPerMinute($timePassed)
    {
        $expectedDuration = $this->messages / ($this->rate / 60);
        return (int) ceil($expectedDuration - $timePassed);
    }
}
