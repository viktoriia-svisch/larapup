<?php
namespace Ramsey\Uuid\Generator;
class PeclUuidTimeGenerator implements TimeGeneratorInterface
{
    public function generate($node = null, $clockSeq = null)
    {
        $uuid = uuid_create(UUID_TYPE_TIME);
        return uuid_parse($uuid);
    }
}
