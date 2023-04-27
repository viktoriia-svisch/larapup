<?php
namespace Ramsey\Uuid\Converter\Time;
use Ramsey\Uuid\Converter\TimeConverterInterface;
class PhpTimeConverter implements TimeConverterInterface
{
    public function calculateTime($seconds, $microSeconds)
    {
        $uuidTime = ($seconds * 10000000) + ($microSeconds * 10) + 0x01b21dd213814000;
        return array(
            'low' => sprintf('%08x', $uuidTime & 0xffffffff),
            'mid' => sprintf('%04x', ($uuidTime >> 32) & 0xffff),
            'hi' => sprintf('%04x', ($uuidTime >> 48) & 0x0fff),
        );
    }
}
