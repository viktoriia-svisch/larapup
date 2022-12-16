<?php
namespace Ramsey\Uuid\Codec;
use Ramsey\Uuid\UuidInterface;
class TimestampFirstCombCodec extends StringCodec
{
    public function encode(UuidInterface $uuid)
    {
        $sixPieceComponents = array_values($uuid->getFieldsHex());
        $this->swapTimestampAndRandomBits($sixPieceComponents);
        return vsprintf(
            '%08s-%04s-%04s-%02s%02s-%012s',
            $sixPieceComponents
        );
    }
    public function encodeBinary(UuidInterface $uuid)
    {
        $stringEncoding = $this->encode($uuid);
        return hex2bin(str_replace('-', '', $stringEncoding));
    }
    public function decode($encodedUuid)
    {
        $fivePieceComponents = $this->extractComponents($encodedUuid);
        $this->swapTimestampAndRandomBits($fivePieceComponents);
        return $this->getBuilder()->build($this, $this->getFields($fivePieceComponents));
    }
    public function decodeBytes($bytes)
    {
        return $this->decode(bin2hex($bytes));
    }
    protected function swapTimestampAndRandomBits(array &$components)
    {
        $last48Bits = $components[4];
        if (count($components) == 6) {
            $last48Bits = $components[5];
            $components[5] = $components[0] . $components[1];
        } else {
            $components[4] = $components[0] . $components[1];
        }
        $components[0] = substr($last48Bits, 0, 8);
        $components[1] = substr($last48Bits, 8, 4);
    }
}
