<?php
namespace Illuminate\Database\Query;
use InvalidArgumentException;
class JsonExpression extends Expression
{
    public function __construct($value)
    {
        parent::__construct(
            $this->getJsonBindingParameter($value)
        );
    }
    protected function getJsonBindingParameter($value)
    {
        if ($value instanceof Expression) {
            return $value->getValue();
        }
        switch ($type = gettype($value)) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'NULL':
            case 'integer':
            case 'double':
            case 'string':
                return '?';
            case 'object':
            case 'array':
                return '?';
        }
        throw new InvalidArgumentException("JSON value is of illegal type: {$type}");
    }
}
