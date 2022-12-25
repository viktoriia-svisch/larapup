<?php
namespace Ramsey\Uuid\Generator;
use Ramsey\Uuid\BinaryUtils;
use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Provider\NodeProviderInterface;
use Ramsey\Uuid\Provider\TimeProviderInterface;
class DefaultTimeGenerator implements TimeGeneratorInterface
{
    private $nodeProvider;
    private $timeConverter;
    private $timeProvider;
    public function __construct(
        NodeProviderInterface $nodeProvider,
        TimeConverterInterface $timeConverter,
        TimeProviderInterface $timeProvider
    ) {
        $this->nodeProvider = $nodeProvider;
        $this->timeConverter = $timeConverter;
        $this->timeProvider = $timeProvider;
    }
    public function generate($node = null, $clockSeq = null)
    {
        $node = $this->getValidNode($node);
        if ($clockSeq === null) {
            $clockSeq = random_int(0, 0x3fff);
        }
        $timeOfDay = $this->timeProvider->currentTime();
        $uuidTime = $this->timeConverter->calculateTime($timeOfDay['sec'], $timeOfDay['usec']);
        $timeHi = BinaryUtils::applyVersion($uuidTime['hi'], 1);
        $clockSeqHi = BinaryUtils::applyVariant($clockSeq >> 8);
        $hex = vsprintf(
            '%08s%04s%04s%02s%02s%012s',
            array(
                $uuidTime['low'],
                $uuidTime['mid'],
                sprintf('%04x', $timeHi),
                sprintf('%02x', $clockSeqHi),
                sprintf('%02x', $clockSeq & 0xff),
                $node,
            )
        );
        return hex2bin($hex);
    }
    protected function getValidNode($node)
    {
        if ($node === null) {
            $node = $this->nodeProvider->getNode();
        }
        if (is_int($node)) {
            $node = sprintf('%012x', $node);
        }
        if (!ctype_xdigit($node) || strlen($node) > 12) {
            throw new \InvalidArgumentException('Invalid node value');
        }
        return strtolower(sprintf('%012s', $node));
    }
}
