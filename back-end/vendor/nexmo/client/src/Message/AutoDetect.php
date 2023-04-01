<?php
namespace Nexmo\Message;
class AutoDetect extends Message
{
    const TYPE = 'text';
    protected $text;
    public function __construct($to, $from, $text, $additional = [])
    {
        parent::__construct($to, $from, $additional);
        $this->enableEncodingDetection();
        $this->requestData['text'] = (string) $text;
    }
}
