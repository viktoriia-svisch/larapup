<?php
namespace Ramsey\Uuid;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Provider\NodeProviderInterface;
use Ramsey\Uuid\Generator\RandomGeneratorInterface;
use Ramsey\Uuid\Generator\TimeGeneratorInterface;
use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Builder\UuidBuilderInterface;
class UuidFactory implements UuidFactoryInterface
{
    private $codec = null;
    private $nodeProvider = null;
    private $numberConverter = null;
    private $randomGenerator = null;
    private $timeGenerator = null;
    private $uuidBuilder = null;
    public function __construct(FeatureSet $features = null)
    {
        $features = $features ?: new FeatureSet();
        $this->codec = $features->getCodec();
        $this->nodeProvider = $features->getNodeProvider();
        $this->numberConverter = $features->getNumberConverter();
        $this->randomGenerator = $features->getRandomGenerator();
        $this->timeGenerator = $features->getTimeGenerator();
        $this->uuidBuilder = $features->getBuilder();
    }
    public function getCodec()
    {
        return $this->codec;
    }
    public function setCodec(CodecInterface $codec)
    {
        $this->codec = $codec;
    }
    public function getNodeProvider()
    {
        return $this->nodeProvider;
    }
    public function getRandomGenerator()
    {
        return $this->randomGenerator;
    }
    public function getTimeGenerator()
    {
        return $this->timeGenerator;
    }
    public function setTimeGenerator(TimeGeneratorInterface $generator)
    {
        $this->timeGenerator = $generator;
    }
    public function getNumberConverter()
    {
        return $this->numberConverter;
    }
    public function setRandomGenerator(RandomGeneratorInterface $generator)
    {
        $this->randomGenerator = $generator;
    }
    public function setNumberConverter(NumberConverterInterface $converter)
    {
        $this->numberConverter = $converter;
    }
    public function getUuidBuilder()
    {
        return $this->uuidBuilder;
    }
    public function setUuidBuilder(UuidBuilderInterface $builder)
    {
        $this->uuidBuilder = $builder;
    }
    public function fromBytes($bytes)
    {
        return $this->codec->decodeBytes($bytes);
    }
    public function fromString($uuid)
    {
        $uuid = strtolower($uuid);
        return $this->codec->decode($uuid);
    }
    public function fromInteger($integer)
    {
        $hex = $this->numberConverter->toHex($integer);
        $hex = str_pad($hex, 32, '0', STR_PAD_LEFT);
        return $this->fromString($hex);
    }
    public function uuid1($node = null, $clockSeq = null)
    {
        $bytes = $this->timeGenerator->generate($node, $clockSeq);
        $hex = bin2hex($bytes);
        return $this->uuidFromHashedName($hex, 1);
    }
    public function uuid3($ns, $name)
    {
        return $this->uuidFromNsAndName($ns, $name, 3, 'md5');
    }
    public function uuid4()
    {
        $bytes = $this->randomGenerator->generate(16);
        $hex = bin2hex($bytes);
        return $this->uuidFromHashedName($hex, 4);
    }
    public function uuid5($ns, $name)
    {
        return $this->uuidFromNsAndName($ns, $name, 5, 'sha1');
    }
    public function uuid(array $fields)
    {
        return $this->uuidBuilder->build($this->codec, $fields);
    }
    protected function uuidFromNsAndName($ns, $name, $version, $hashFunction)
    {
        if (!($ns instanceof UuidInterface)) {
            $ns = $this->codec->decode($ns);
        }
        $hash = call_user_func($hashFunction, ($ns->getBytes() . $name));
        return $this->uuidFromHashedName($hash, $version);
    }
    protected function uuidFromHashedName($hash, $version)
    {
        $timeHi = BinaryUtils::applyVersion(substr($hash, 12, 4), $version);
        $clockSeqHi = BinaryUtils::applyVariant(hexdec(substr($hash, 16, 2)));
        $fields = array(
            'time_low' => substr($hash, 0, 8),
            'time_mid' => substr($hash, 8, 4),
            'time_hi_and_version' => str_pad(dechex($timeHi), 4, '0', STR_PAD_LEFT),
            'clock_seq_hi_and_reserved' => str_pad(dechex($clockSeqHi), 2, '0', STR_PAD_LEFT),
            'clock_seq_low' => substr($hash, 18, 2),
            'node' => substr($hash, 20, 12),
        );
        return $this->uuid($fields);
    }
}
