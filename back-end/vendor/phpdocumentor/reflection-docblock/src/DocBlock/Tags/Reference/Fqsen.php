<?php
namespace phpDocumentor\Reflection\DocBlock\Tags\Reference;
use phpDocumentor\Reflection\Fqsen as RealFqsen;
final class Fqsen implements Reference
{
    private $fqsen;
    public function __construct(RealFqsen $fqsen)
    {
        $this->fqsen = $fqsen;
    }
    public function __toString()
    {
        return (string)$this->fqsen;
    }
}
