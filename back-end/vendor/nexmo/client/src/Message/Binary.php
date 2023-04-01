<?php
namespace Nexmo\Message;
class Binary extends Message
{
    const TYPE = 'binary';
    protected $body;
    protected $udh;
    public function __construct($to, $from, $body, $udh)
    {
        parent::__construct($to, $from);
        $this->body = (string) $body;
        $this->udh =  (string) $udh;
    }
    public function getRequestData($sent = true)
    {
        return array_merge(parent::getRequestData($sent), array(
            'body' => $this->body,
            'udh'  => $this->udh,
        ));
    }
}
