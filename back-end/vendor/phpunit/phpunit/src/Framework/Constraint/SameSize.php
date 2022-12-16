<?php
namespace PHPUnit\Framework\Constraint;
class SameSize extends Count
{
    public function __construct(iterable $expected)
    {
        parent::__construct($this->getCountOf($expected));
    }
}
