<?php
namespace Ramsey\Uuid\Codec;
use InvalidArgumentException;
use Ramsey\Uuid\Builder\UuidBuilderInterface;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
class StringCodec implements CodecInterface
{
    private $builder;
    public function __construct(UuidBuilderInterface $builder)
    {
        $this->builder = $builder;
    }
    public function encode(UuidInterface $uuid)
    {
        $fields = array_values($uuid->getFieldsHex());
        return vsprintf(
            '%08s-%04s-%04s-%02s%02s-%012s',
            $fields
        );
    }
    public function encodeBinary(UuidInterface $uuid)
    {
        return hex2bin($uuid->getHex());
    }
    public function decode($encodedUuid)
    {
        $components = $this->extractComponents($encodedUuid);
        $fields = $this->getFields($components);
        return $this->builder->build($this, $fields);
    }
    public function decodeBytes($bytes)
    {
        if (strlen($bytes) !== 16) {
            throw new InvalidArgumentException('$bytes string should contain 16 characters.');
        }
        $hexUuid = unpack('H*', $bytes);
        return $this->decode($hexUuid[1]);
    }
    protected function getBuilder()
    {
        return $this->builder;
    }
    protected function extractComponents($encodedUuid)
    {
        $nameParsed = str_replace(array(
            'urn:',
            'uuid:',
            '{',
            '}',
            '-'
        ), '', $encodedUuid);
        $components = array(
            substr($nameParsed, 0, 8),
            substr($nameParsed, 8, 4),
            substr($nameParsed, 12, 4),
            substr($nameParsed, 16, 4),
            substr($nameParsed, 20)
        );
        $nameParsed = implode('-', $components);
        if (!Uuid::isValid($nameParsed)) {
            throw new InvalidUuidStringException('Invalid UUID string: ' . $encodedUuid);
        }
        return $components;
    }
    protected function getFields(array $components)
    {
        return array(
            'time_low' => str_pad($components[0], 8, '0', STR_PAD_LEFT),
            'time_mid' => str_pad($components[1], 4, '0', STR_PAD_LEFT),
            'time_hi_and_version' => str_pad($components[2], 4, '0', STR_PAD_LEFT),
            'clock_seq_hi_and_reserved' => str_pad(substr($components[3], 0, 2), 2, '0', STR_PAD_LEFT),
            'clock_seq_low' => str_pad(substr($components[3], 2), 2, '0', STR_PAD_LEFT),
            'node' => str_pad($components[4], 12, '0', STR_PAD_LEFT)
        );
    }
}
