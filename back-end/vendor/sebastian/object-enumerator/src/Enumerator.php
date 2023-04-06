<?php
namespace SebastianBergmann\ObjectEnumerator;
use SebastianBergmann\ObjectReflector\ObjectReflector;
use SebastianBergmann\RecursionContext\Context;
class Enumerator
{
    public function enumerate($variable)
    {
        if (!is_array($variable) && !is_object($variable)) {
            throw new InvalidArgumentException;
        }
        if (isset(func_get_args()[1])) {
            if (!func_get_args()[1] instanceof Context) {
                throw new InvalidArgumentException;
            }
            $processed = func_get_args()[1];
        } else {
            $processed = new Context;
        }
        $objects = [];
        if ($processed->contains($variable)) {
            return $objects;
        }
        $array = $variable;
        $processed->add($variable);
        if (is_array($variable)) {
            foreach ($array as $element) {
                if (!is_array($element) && !is_object($element)) {
                    continue;
                }
                $objects = array_merge(
                    $objects,
                    $this->enumerate($element, $processed)
                );
            }
        } else {
            $objects[] = $variable;
            $reflector = new ObjectReflector;
            foreach ($reflector->getAttributes($variable) as $value) {
                if (!is_array($value) && !is_object($value)) {
                    continue;
                }
                $objects = array_merge(
                    $objects,
                    $this->enumerate($value, $processed)
                );
            }
        }
        return $objects;
    }
}
