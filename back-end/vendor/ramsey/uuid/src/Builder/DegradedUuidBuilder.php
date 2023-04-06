<?php
namespace Ramsey\Uuid\Builder;
use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\DegradedUuid;
class DegradedUuidBuilder implements UuidBuilderInterface
{
    private $converter;
    public function __construct(NumberConverterInterface $converter)
    {
        $this->converter = $converter;
    }
    public function build(CodecInterface $codec, array $fields)
    {
        return new DegradedUuid($fields, $this->converter, $codec);
    }
}
