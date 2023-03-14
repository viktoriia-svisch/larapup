<?php
namespace Nexmo\Message;
class Wap extends Message
{
    const TYPE = 'wappush';
    protected $title;
    protected $url;
    protected $validity;
    public function __construct($to, $from, $title, $url, $validity)
    {
        parent::__construct($to, $from);
        $this->title    = (string) $title;
        $this->url      =  (string) $url;
        $this->validity = (int) $validity;
    }
    public function getRequestData($sent = true)
    {
        return array_merge(parent::getRequestData($sent), array(
            'title'      => $this->title,
            'url'        => $this->url,
            'validity'   => $this->validity,
        ));
    }
}
