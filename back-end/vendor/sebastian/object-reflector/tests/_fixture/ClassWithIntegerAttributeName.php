<?php
declare(strict_types=1);
namespace SebastianBergmann\ObjectReflector\TestFixture;
class ClassWithIntegerAttributeName
{
    public function __construct()
    {
        $i        = 1;
        $this->$i = 2;
    }
}
