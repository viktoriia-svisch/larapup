<?php
namespace Nexmo\Call;
class Hangup implements \JsonSerializable
{
    function jsonSerialize()
    {
        return [
            'action' => 'hangup'
        ];
    }
}
