<?php
namespace Nexmo\Call;
class Transfer implements \JsonSerializable
{
    protected $urls;
    public function __construct($urls)
    {
        if(!is_array($urls)){
            $urls = array($urls);
        }
        $this->urls = $urls;
    }
    function jsonSerialize()
    {
        return [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'url' => $this->urls
            ]
        ];
    }
}
