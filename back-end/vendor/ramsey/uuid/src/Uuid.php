<?php
namespace Ramsey\Uuid;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
class Uuid implements UuidInterface
{
    const NAMESPACE_DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
    const NAMESPACE_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';
    const NAMESPACE_OID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';
    const NAMESPACE_X500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';
    const NIL = '00000000-0000-0000-0000-000000000000';
    const RESERVED_NCS = 0;
    const RFC_4122 = 2;
    const RESERVED_MICROSOFT = 6;
    const RESERVED_FUTURE = 7;
    const VALID_PATTERN = '^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$';
    const UUID_TYPE_TIME = 1;
    const UUID_TYPE_IDENTIFIER = 2;
    const UUID_TYPE_HASH_MD5 = 3;
    const UUID_TYPE_RANDOM = 4;
    const UUID_TYPE_HASH_SHA1 = 5;
    private static $factory = null;
    protected $codec;
    protected $fields = array(
        'time_low' => '00000000',
        'time_mid' => '0000',
        'time_hi_and_version' => '0000',
        'clock_seq_hi_and_reserved' => '00',
        'clock_seq_low' => '00',
        'node' => '000000000000',
    );
    protected $converter;
    public function __construct(
        array $fields,
        NumberConverterInterface $converter,
        CodecInterface $codec
    ) {
        $this->fields = $fields;
        $this->codec = $codec;
        $this->converter = $converter;
    }
    public function __toString()
    {
        return $this->toString();
    }
    public function jsonSerialize()
    {
        return $this->toString();
    }
    public function serialize()
    {
        return $this->toString();
    }
    public function unserialize($serialized)
    {
        $uuid = self::fromString($serialized);
        $this->codec = $uuid->codec;
        $this->converter = $uuid->converter;
        $this->fields = $uuid->fields;
    }
    public function compareTo(UuidInterface $other)
    {
        $comparison = 0;
        if ($this->getMostSignificantBitsHex() < $other->getMostSignificantBitsHex()) {
            $comparison = -1;
        } elseif ($this->getMostSignificantBitsHex() > $other->getMostSignificantBitsHex()) {
            $comparison = 1;
        } elseif ($this->getLeastSignificantBitsHex() < $other->getLeastSignificantBitsHex()) {
            $comparison = -1;
        } elseif ($this->getLeastSignificantBitsHex() > $other->getLeastSignificantBitsHex()) {
            $comparison = 1;
        }
        return $comparison;
    }
    public function equals($other)
    {
        if (!($other instanceof UuidInterface)) {
            return false;
        }
        return ($this->compareTo($other) == 0);
    }
    public function getBytes()
    {
        return $this->codec->encodeBinary($this);
    }
    public function getClockSeqHiAndReserved()
    {
        return hexdec($this->getClockSeqHiAndReservedHex());
    }
    public function getClockSeqHiAndReservedHex()
    {
        return $this->fields['clock_seq_hi_and_reserved'];
    }
    public function getClockSeqLow()
    {
        return hexdec($this->getClockSeqLowHex());
    }
    public function getClockSeqLowHex()
    {
        return $this->fields['clock_seq_low'];
    }
    public function getClockSequence()
    {
        return (($this->getClockSeqHiAndReserved() & 0x3f) << 8)
            | $this->getClockSeqLow();
    }
    public function getClockSequenceHex()
    {
        return sprintf('%04x', $this->getClockSequence());
    }
    public function getNumberConverter()
    {
        return $this->converter;
    }
    public function getDateTime()
    {
        if ($this->getVersion() != 1) {
            throw new UnsupportedOperationException('Not a time-based UUID');
        }
        $unixTime = ($this->getTimestamp() - 0x01b21dd213814000) / 1e7;
        $unixTime = number_format($unixTime, 0, '', '');
        return new \DateTime("@{$unixTime}");
    }
    public function getFields()
    {
        return array(
            'time_low' => $this->getTimeLow(),
            'time_mid' => $this->getTimeMid(),
            'time_hi_and_version' => $this->getTimeHiAndVersion(),
            'clock_seq_hi_and_reserved' => $this->getClockSeqHiAndReserved(),
            'clock_seq_low' => $this->getClockSeqLow(),
            'node' => $this->getNode(),
        );
    }
    public function getFieldsHex()
    {
        return $this->fields;
    }
    public function getHex()
    {
        return str_replace('-', '', $this->toString());
    }
    public function getInteger()
    {
        return $this->converter->fromHex($this->getHex());
    }
    public function getLeastSignificantBits()
    {
        return $this->converter->fromHex($this->getLeastSignificantBitsHex());
    }
    public function getLeastSignificantBitsHex()
    {
        return sprintf(
            '%02s%02s%012s',
            $this->fields['clock_seq_hi_and_reserved'],
            $this->fields['clock_seq_low'],
            $this->fields['node']
        );
    }
    public function getMostSignificantBits()
    {
        return $this->converter->fromHex($this->getMostSignificantBitsHex());
    }
    public function getMostSignificantBitsHex()
    {
        return sprintf(
            '%08s%04s%04s',
            $this->fields['time_low'],
            $this->fields['time_mid'],
            $this->fields['time_hi_and_version']
        );
    }
    public function getNode()
    {
        return hexdec($this->getNodeHex());
    }
    public function getNodeHex()
    {
        return $this->fields['node'];
    }
    public function getTimeHiAndVersion()
    {
        return hexdec($this->getTimeHiAndVersionHex());
    }
    public function getTimeHiAndVersionHex()
    {
        return $this->fields['time_hi_and_version'];
    }
    public function getTimeLow()
    {
        return hexdec($this->getTimeLowHex());
    }
    public function getTimeLowHex()
    {
        return $this->fields['time_low'];
    }
    public function getTimeMid()
    {
        return hexdec($this->getTimeMidHex());
    }
    public function getTimeMidHex()
    {
        return $this->fields['time_mid'];
    }
    public function getTimestamp()
    {
        if ($this->getVersion() != 1) {
            throw new UnsupportedOperationException('Not a time-based UUID');
        }
        return hexdec($this->getTimestampHex());
    }
    public function getTimestampHex()
    {
        if ($this->getVersion() != 1) {
            throw new UnsupportedOperationException('Not a time-based UUID');
        }
        return sprintf(
            '%03x%04s%08s',
            ($this->getTimeHiAndVersion() & 0x0fff),
            $this->fields['time_mid'],
            $this->fields['time_low']
        );
    }
    public function getUrn()
    {
        return 'urn:uuid:' . $this->toString();
    }
    public function getVariant()
    {
        $clockSeq = $this->getClockSeqHiAndReserved();
        if (0 === ($clockSeq & 0x80)) {
            $variant = self::RESERVED_NCS;
        } elseif (0 === ($clockSeq & 0x40)) {
            $variant = self::RFC_4122;
        } elseif (0 === ($clockSeq & 0x20)) {
            $variant = self::RESERVED_MICROSOFT;
        } else {
            $variant = self::RESERVED_FUTURE;
        }
        return $variant;
    }
    public function getVersion()
    {
        if ($this->getVariant() == self::RFC_4122) {
            return (int) (($this->getTimeHiAndVersion() >> 12) & 0x0f);
        }
        return null;
    }
    public function toString()
    {
        return $this->codec->encode($this);
    }
    public static function getFactory()
    {
        if (!self::$factory) {
            self::$factory = new UuidFactory();
        }
        return self::$factory;
    }
    public static function setFactory(UuidFactoryInterface $factory)
    {
        self::$factory = $factory;
    }
    public static function fromBytes($bytes)
    {
        return self::getFactory()->fromBytes($bytes);
    }
    public static function fromString($name)
    {
        return self::getFactory()->fromString($name);
    }
    public static function fromInteger($integer)
    {
        return self::getFactory()->fromInteger($integer);
    }
    public static function isValid($uuid)
    {
        $uuid = str_replace(array('urn:', 'uuid:', '{', '}'), '', $uuid);
        if ($uuid == self::NIL) {
            return true;
        }
        if (!preg_match('/' . self::VALID_PATTERN . '/D', $uuid)) {
            return false;
        }
        return true;
    }
    public static function uuid1($node = null, $clockSeq = null)
    {
        return self::getFactory()->uuid1($node, $clockSeq);
    }
    public static function uuid3($ns, $name)
    {
        return self::getFactory()->uuid3($ns, $name);
    }
    public static function uuid4()
    {
        return self::getFactory()->uuid4();
    }
    public static function uuid5($ns, $name)
    {
        return self::getFactory()->uuid5($ns, $name);
    }
}
