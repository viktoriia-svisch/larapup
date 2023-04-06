<?php
namespace Nexmo\Message;
class Text extends Message
{
    const TYPE = 'text';
    protected $text;
    public function __construct($to, $from, $text, $additional = [])
    {
        parent::__construct($to, $from, $additional);
        $this->requestData['text'] = (string) $text;
    }
}
