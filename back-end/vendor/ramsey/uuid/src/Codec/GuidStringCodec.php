<?php
namespace Ramsey\Uuid\Codec;
use Ramsey\Uuid\UuidInterface;
class GuidStringCodec extends StringCodec
{
    public function encode(UuidInterface $uuid)
    {
        $components = array_values($uuid->getFieldsHex());
        $this->swapFields($components);
        return vsprintf(
            '%08s-%04s-%04s-%02s%02s-%012s',
            $components
        );
    }
    public function encodeBinary(UuidInterface $uuid)
    {
        $components = array_values($uuid->getFieldsHex());
        return hex2bin(implode('', $components));
    }
    public function decode($encodedUuid)
    {
        $components = $this->extractComponents($encodedUuid);
        $this->swapFields($components);
        return $this->getBuilder()->build($this, $this->getFields($components));
    }
    public function decodeBytes($bytes)
    {
        return parent::decode(bin2hex($bytes));
    }
    protected function swapFields(array &$components)
    {
        $hex = unpack('H*', pack('L', hexdec($components[0])));
        $components[0] = $hex[1];
        $hex = unpack('H*', pack('S', hexdec($components[1])));
        $components[1] = $hex[1];
        $hex = unpack('H*', pack('S', hexdec($components[2])));
        $components[2] = $hex[1];
    }
}
