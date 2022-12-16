<?php
namespace Prophecy\Prophecy;
class Revealer implements RevealerInterface
{
    public function reveal($value)
    {
        if (is_array($value)) {
            return array_map(array($this, __FUNCTION__), $value);
        }
        if (!is_object($value)) {
            return $value;
        }
        if ($value instanceof ProphecyInterface) {
            $value = $value->reveal();
        }
        return $value;
    }
}
