<?php
namespace Symfony\Component\VarDumper\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;
class ConstStub extends Stub
{
    public function __construct(string $name, $value)
    {
        $this->class = $name;
        $this->value = $value;
    }
    public function __toString()
    {
        return (string) $this->value;
    }
}
