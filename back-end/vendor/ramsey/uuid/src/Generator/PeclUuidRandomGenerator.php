<?php
namespace Ramsey\Uuid\Generator;
class PeclUuidRandomGenerator implements RandomGeneratorInterface
{
    public function generate($length)
    {
        $uuid = uuid_create(UUID_TYPE_RANDOM);
        return uuid_parse($uuid);
    }
}
