<?php
namespace Nexmo\Call;
class Mute implements \JsonSerializable
{
    function jsonSerialize()
    {
        return [
            'action' => 'mute'
        ];
    }
}
