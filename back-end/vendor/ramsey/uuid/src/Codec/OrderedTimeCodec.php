<?php
namespace Ramsey\Uuid\Codec;
use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;
class OrderedTimeCodec extends StringCodec
{
    public function encodeBinary(UuidInterface $uuid)
    {
        $fields = $uuid->getFieldsHex();
        $optimized = [
            $fields['time_hi_and_version'],
            $fields['time_mid'],
            $fields['time_low'],
            $fields['clock_seq_hi_and_reserved'],
            $fields['clock_seq_low'],
            $fields['node'],
        ];
        return hex2bin(implode('', $optimized));
    }
    public function decodeBytes($bytes)
    {
        if (strlen($bytes) !== 16) {
            throw new InvalidArgumentException('$bytes string should contain 16 characters.');
        }
        $hex = unpack('H*', $bytes)[1];
        $hex = substr($hex, 8, 4) . substr($hex, 12, 4) . substr($hex, 4, 4) . substr($hex, 0, 4) . substr($hex, 16);
        return $this->decode($hex);
    }
}
