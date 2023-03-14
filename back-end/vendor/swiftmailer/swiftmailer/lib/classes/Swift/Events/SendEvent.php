<?php
class Swift_Events_SendEvent extends Swift_Events_EventObject
{
    const RESULT_PENDING = 0x0001;
    const RESULT_SPOOLED = 0x0011;
    const RESULT_SUCCESS = 0x0010;
    const RESULT_TENTATIVE = 0x0100;
    const RESULT_FAILED = 0x1000;
    private $message;
    private $failedRecipients = [];
    private $result;
    public function __construct(Swift_Transport $source, Swift_Mime_SimpleMessage $message)
    {
        parent::__construct($source);
        $this->message = $message;
        $this->result = self::RESULT_PENDING;
    }
    public function getTransport()
    {
        return $this->getSource();
    }
    public function getMessage()
    {
        return $this->message;
    }
    public function setFailedRecipients($recipients)
    {
        $this->failedRecipients = $recipients;
    }
    public function getFailedRecipients()
    {
        return $this->failedRecipients;
    }
    public function setResult($result)
    {
        $this->result = $result;
    }
    public function getResult()
    {
        return $this->result;
    }
}
