<?php
namespace Monolog\Handler;
use Gelf\MessagePublisher;
use Gelf\Message;
class GelfMockMessagePublisher extends MessagePublisher
{
    public function publish(Message $message)
    {
        $this->lastMessage = $message;
    }
    public $lastMessage = null;
}
