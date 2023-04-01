<?php
namespace phpDocumentor\Reflection\Types;
use phpDocumentor\Reflection\Type;
final class Resource_ implements Type
{
    public function __toString()
    {
        return 'resource';
    }
}
