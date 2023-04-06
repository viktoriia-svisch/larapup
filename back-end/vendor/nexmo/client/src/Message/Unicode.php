<?php
namespace Nexmo\Message;
class Unicode extends Message
{
    const TYPE = 'unicode';
    protected $text;
    public function __construct($to, $from, $text)
    {
        parent::__construct($to, $from);
        $this->text = (string) $text;
    }
    public function getRequestData($sent = true)
    {
        return array_merge(parent::getRequestData($sent), array(
            'text' => $this->text
        ));        
    }
}
