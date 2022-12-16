<?php
namespace Ramsey\Uuid;
class BinaryUtils
{
    public static function applyVariant($clockSeqHi)
    {
        $clockSeqHi = $clockSeqHi & 0x3f;
        $clockSeqHi &= ~(0xc0);
        $clockSeqHi |= 0x80;
        return $clockSeqHi;
    }
    public static function applyVersion($timeHi, $version)
    {
        $timeHi = hexdec($timeHi) & 0x0fff;
        $timeHi &= ~(0xf000);
        $timeHi |= $version << 12;
        return $timeHi;
    }
}
