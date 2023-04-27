<?php
namespace Nexmo\Message;
class Vcal extends Message
{
    const TYPE = 'vcal';
    protected $vcal;
    public function __construct($to, $from, $vcal)
    {
        parent::__construct($to, $from);
        $this->text = (string) $vcal;
    }
    public function getRequestData($sent = true)
    {
        return array_merge(parent::getRequestData($sent), array(
            'vcal' => $this->vcal
        ));        
    }
}
