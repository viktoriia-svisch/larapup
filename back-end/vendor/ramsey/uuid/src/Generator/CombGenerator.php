<?php
namespace Ramsey\Uuid\Generator;
use Ramsey\Uuid\Converter\NumberConverterInterface;
class CombGenerator implements RandomGeneratorInterface
{
    const TIMESTAMP_BYTES = 6;
    private $randomGenerator;
    private $converter;
    public function __construct(RandomGeneratorInterface $generator, NumberConverterInterface $numberConverter)
    {
        $this->converter = $numberConverter;
        $this->randomGenerator = $generator;
    }
    public function generate($length)
    {
        if ($length < self::TIMESTAMP_BYTES || $length < 0) {
            throw new \InvalidArgumentException('Length must be a positive integer.');
        }
        $hash = '';
        if (self::TIMESTAMP_BYTES > 0 && $length > self::TIMESTAMP_BYTES) {
            $hash = $this->randomGenerator->generate($length - self::TIMESTAMP_BYTES);
        }
        $lsbTime = str_pad($this->converter->toHex($this->timestamp()), self::TIMESTAMP_BYTES * 2, '0', STR_PAD_LEFT);
        return hex2bin(str_pad(bin2hex($hash), $length - self::TIMESTAMP_BYTES, '0') . $lsbTime);
    }
    private function timestamp()
    {
        $time = explode(' ', microtime(false));
        return $time[1] . substr($time[0], 2, 5);
    }
}
