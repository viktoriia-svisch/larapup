<?php
namespace Psy\Test;
class ClassWithSecrets
{
    private const PRIVATE_CONST = 'private and const';
    private static $privateStaticProp = 'private and static and prop';
    private $privateProp = 'private and prop';
    private static function privateStaticMethod($extra = null)
    {
        if ($extra !== null) {
            return 'private and static and method with ' . \json_encode($extra);
        }
        return 'private and static and method';
    }
    private function privateMethod($extra = null)
    {
        if ($extra !== null) {
            return 'private and method with ' . \json_encode($extra);
        }
        return 'private and method';
    }
}
