<?php
namespace Nexmo\Call;
class Unearmuff implements \JsonSerializable
{
    function jsonSerialize()
    {
        return [
            'action' => 'unearmuff'
        ];
    }
}
