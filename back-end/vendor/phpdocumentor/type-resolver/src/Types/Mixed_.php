<?php
namespace phpDocumentor\Reflection\Types;
use phpDocumentor\Reflection\Type;
final class Mixed_ implements Type
{
    public function __toString()
    {
        return 'mixed';
    }
}
