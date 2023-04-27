<?php
namespace Ramsey\Uuid\Codec;
use Ramsey\Uuid\UuidInterface;
interface CodecInterface
{
    public function encode(UuidInterface $uuid);
    public function encodeBinary(UuidInterface $uuid);
    public function decode($encodedUuid);
    public function decodeBytes($bytes);
}
