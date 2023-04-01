<?php
namespace Ramsey\Uuid;
use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Generator\PeclUuidTimeGenerator;
use Ramsey\Uuid\Provider\Node\FallbackNodeProvider;
use Ramsey\Uuid\Provider\Node\RandomNodeProvider;
use Ramsey\Uuid\Provider\Node\SystemNodeProvider;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Converter\Number\BigNumberConverter;
use Ramsey\Uuid\Converter\Number\DegradedNumberConverter;
use Ramsey\Uuid\Converter\Time\BigNumberTimeConverter;
use Ramsey\Uuid\Converter\Time\DegradedTimeConverter;
use Ramsey\Uuid\Converter\Time\PhpTimeConverter;
use Ramsey\Uuid\Provider\Time\SystemTimeProvider;
use Ramsey\Uuid\Builder\UuidBuilderInterface;
use Ramsey\Uuid\Builder\DefaultUuidBuilder;
use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Codec\StringCodec;
use Ramsey\Uuid\Codec\GuidStringCodec;
use Ramsey\Uuid\Builder\DegradedUuidBuilder;
use Ramsey\Uuid\Generator\RandomGeneratorFactory;
use Ramsey\Uuid\Generator\RandomGeneratorInterface;
use Ramsey\Uuid\Generator\TimeGeneratorFactory;
use Ramsey\Uuid\Generator\TimeGeneratorInterface;
use Ramsey\Uuid\Provider\TimeProviderInterface;
use Ramsey\Uuid\Provider\NodeProviderInterface;
class FeatureSet
{
    private $disableBigNumber = false;
    private $disable64Bit = false;
    private $ignoreSystemNode = false;
    private $enablePecl = false;
    private $builder;
    private $codec;
    private $nodeProvider;
    private $numberConverter;
    private $randomGenerator;
    private $timeGenerator;
    public function __construct(
        $useGuids = false,
        $force32Bit = false,
        $forceNoBigNumber = false,
        $ignoreSystemNode = false,
        $enablePecl = false
    ) {
        $this->disableBigNumber = $forceNoBigNumber;
        $this->disable64Bit = $force32Bit;
        $this->ignoreSystemNode = $ignoreSystemNode;
        $this->enablePecl = $enablePecl;
        $this->numberConverter = $this->buildNumberConverter();
        $this->builder = $this->buildUuidBuilder();
        $this->codec = $this->buildCodec($useGuids);
        $this->nodeProvider = $this->buildNodeProvider();
        $this->randomGenerator = $this->buildRandomGenerator();
        $this->setTimeProvider(new SystemTimeProvider());
    }
    public function getBuilder()
    {
        return $this->builder;
    }
    public function getCodec()
    {
        return $this->codec;
    }
    public function getNodeProvider()
    {
        return $this->nodeProvider;
    }
    public function getNumberConverter()
    {
        return $this->numberConverter;
    }
    public function getRandomGenerator()
    {
        return $this->randomGenerator;
    }
    public function getTimeGenerator()
    {
        return $this->timeGenerator;
    }
    public function setTimeProvider(TimeProviderInterface $timeProvider)
    {
        $this->timeGenerator = $this->buildTimeGenerator($timeProvider);
    }
    protected function buildCodec($useGuids = false)
    {
        if ($useGuids) {
            return new GuidStringCodec($this->builder);
        }
        return new StringCodec($this->builder);
    }
    protected function buildNodeProvider()
    {
        if ($this->ignoreSystemNode) {
            return new RandomNodeProvider();
        }
        return new FallbackNodeProvider([
            new SystemNodeProvider(),
            new RandomNodeProvider()
        ]);
    }
    protected function buildNumberConverter()
    {
        if ($this->hasBigNumber()) {
            return new BigNumberConverter();
        }
        return new DegradedNumberConverter();
    }
    protected function buildRandomGenerator()
    {
        return (new RandomGeneratorFactory())->getGenerator();
    }
    protected function buildTimeGenerator(TimeProviderInterface $timeProvider)
    {
        if ($this->enablePecl) {
            return new PeclUuidTimeGenerator();
        }
        return (new TimeGeneratorFactory(
            $this->nodeProvider,
            $this->buildTimeConverter(),
            $timeProvider
        ))->getGenerator();
    }
    protected function buildTimeConverter()
    {
        if ($this->is64BitSystem()) {
            return new PhpTimeConverter();
        } elseif ($this->hasBigNumber()) {
            return new BigNumberTimeConverter();
        }
        return new DegradedTimeConverter();
    }
    protected function buildUuidBuilder()
    {
        if ($this->is64BitSystem()) {
            return new DefaultUuidBuilder($this->numberConverter);
        }
        return new DegradedUuidBuilder($this->numberConverter);
    }
    protected function hasBigNumber()
    {
        return class_exists('Moontoast\Math\BigNumber') && !$this->disableBigNumber;
    }
    protected function is64BitSystem()
    {
        return PHP_INT_SIZE == 8 && !$this->disable64Bit;
    }
}
