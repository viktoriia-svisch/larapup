<?php
namespace Nexmo\Call;
class Earmuff implements \JsonSerializable
{
    function jsonSerialize()
    {
        return [
            'action' => 'earmuff'
        ];
    }
}
