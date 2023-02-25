<?php
declare(strict_types=1);
namespace SebastianBergmann\ObjectReflector;
class ObjectReflector
{
    public function getAttributes($object): array
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException;
        }
        $attributes = [];
        $className  = get_class($object);
        foreach ((array) $object as $name => $value) {
            $name = explode("\0", (string) $name);
            if (count($name) === 1) {
                $name = $name[0];
            } else {
                if ($name[1] !== $className) {
                    $name = $name[1] . '::' . $name[2];
                } else {
                    $name = $name[2];
                }
            }
            $attributes[$name] = $value;
        }
        return $attributes;
    }
}
