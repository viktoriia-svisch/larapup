<?php
namespace Nexmo\Message;
class Vcard extends Message
{
    const TYPE = 'vcard';
    protected $vcard;
    public function __construct($to, $from, $vcard)
    {
        parent::__construct($to, $from);
        $this->vcard = (string) $vcard;
    }
    public function getRequestData($sent = true)
    {
        return array_merge(parent::getRequestData($sent), array(
            'vcard' => $this->vcard
        ));
    }
}
