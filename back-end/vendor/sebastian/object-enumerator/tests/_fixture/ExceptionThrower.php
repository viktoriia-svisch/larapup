<?php
namespace SebastianBergmann\ObjectEnumerator\Fixtures;
use RuntimeException;
class ExceptionThrower
{
    private $property;
    public function __construct()
    {
        unset($this->property);
    }
    public function __get($property)
    {
        throw new RuntimeException;
    }
}
