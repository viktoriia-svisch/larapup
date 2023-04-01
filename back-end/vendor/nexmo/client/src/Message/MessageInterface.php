<?php
namespace Nexmo\Message;
interface MessageInterface extends \Nexmo\Entity\EntityInterface
{
    public function getMessageId();
}
