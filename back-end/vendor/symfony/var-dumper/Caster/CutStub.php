<?php
namespace Symfony\Component\VarDumper\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;
class CutStub extends Stub
{
    public function __construct($value)
    {
        $this->value = $value;
        switch (\gettype($value)) {
            case 'object':
                $this->type = self::TYPE_OBJECT;
                $this->class = \get_class($value);
                $this->cut = -1;
                break;
            case 'array':
                $this->type = self::TYPE_ARRAY;
                $this->class = self::ARRAY_ASSOC;
                $this->cut = $this->value = \count($value);
                break;
            case 'resource':
            case 'unknown type':
            case 'resource (closed)':
                $this->type = self::TYPE_RESOURCE;
                $this->handle = (int) $value;
                if ('Unknown' === $this->class = @get_resource_type($value)) {
                    $this->class = 'Closed';
                }
                $this->cut = -1;
                break;
            case 'string':
                $this->type = self::TYPE_STRING;
                $this->class = preg_match('
                $this->cut = self::STRING_BINARY === $this->class ? \strlen($value) : mb_strlen($value, 'UTF-8');
                $this->value = '';
                break;
        }
    }
}
