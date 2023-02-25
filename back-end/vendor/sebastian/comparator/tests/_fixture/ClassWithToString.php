<?php
namespace SebastianBergmann\Comparator;
class ClassWithToString
{
    public function __toString()
    {
        return 'string representation';
    }
}
