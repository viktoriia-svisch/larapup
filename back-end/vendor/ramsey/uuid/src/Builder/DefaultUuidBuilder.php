<?php
namespace Ramsey\Uuid\Builder;
use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Uuid;
class DefaultUuidBuilder implements UuidBuilderInterface
{
    private $converter;
    public function __construct(NumberConverterInterface $converter)
    {
        $this->converter = $converter;
    }
    public function build(CodecInterface $codec, array $fields)
    {
        return new Uuid($fields, $this->converter, $codec);
    }
}
