<?php
namespace SebastianBergmann\Comparator;
class ExceptionComparator extends ObjectComparator
{
    public function accepts($expected, $actual)
    {
        return $expected instanceof \Exception && $actual instanceof \Exception;
    }
    protected function toArray($object)
    {
        $array = parent::toArray($object);
        unset(
            $array['file'],
            $array['line'],
            $array['trace'],
            $array['string'],
            $array['xdebug_message']
        );
        return $array;
    }
}
