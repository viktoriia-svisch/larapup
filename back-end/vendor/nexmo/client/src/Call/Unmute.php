<?php
namespace Nexmo\Call;
class Unmute implements \JsonSerializable
{
    function jsonSerialize()
    {
        return [
            'action' => 'unmute'
        ];
    }
}
