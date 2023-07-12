<?php
namespace Ramsey\Uuid\Builder;
use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\UuidInterface;
interface UuidBuilderInterface
{
    public function build(CodecInterface $codec, array $fields);
}
