<?php
namespace PHPUnit\Framework\Constraint;
class IsType extends Constraint
{
    public const TYPE_ARRAY    = 'array';
    public const TYPE_BOOL     = 'bool';
    public const TYPE_FLOAT    = 'float';
    public const TYPE_INT      = 'int';
    public const TYPE_NULL     = 'null';
    public const TYPE_NUMERIC  = 'numeric';
    public const TYPE_OBJECT   = 'object';
    public const TYPE_RESOURCE = 'resource';
    public const TYPE_STRING   = 'string';
    public const TYPE_SCALAR   = 'scalar';
    public const TYPE_CALLABLE = 'callable';
    public const TYPE_ITERABLE = 'iterable';
    private const KNOWN_TYPES = [
        'array'    => true,
        'boolean'  => true,
        'bool'     => true,
        'double'   => true,
        'float'    => true,
        'integer'  => true,
        'int'      => true,
        'null'     => true,
        'numeric'  => true,
        'object'   => true,
        'real'     => true,
        'resource' => true,
        'string'   => true,
        'scalar'   => true,
        'callable' => true,
        'iterable' => true,
    ];
    private $type;
    public function __construct(string $type)
    {
        parent::__construct();
        if (!isset(self::KNOWN_TYPES[$type])) {
            throw new \PHPUnit\Framework\Exception(
                \sprintf(
                    'Type specified for PHPUnit\Framework\Constraint\IsType <%s> ' .
                    'is not a valid type.',
                    $type
                )
            );
        }
        $this->type = $type;
    }
    public function toString(): string
    {
        return \sprintf(
            'is of type "%s"',
            $this->type
        );
    }
    protected function matches($other): bool
    {
        switch ($this->type) {
            case 'numeric':
                return \is_numeric($other);
            case 'integer':
            case 'int':
                return \is_int($other);
            case 'double':
            case 'float':
            case 'real':
                return \is_float($other);
            case 'string':
                return \is_string($other);
            case 'boolean':
            case 'bool':
                return \is_bool($other);
            case 'null':
                return null === $other;
            case 'array':
                return \is_array($other);
            case 'object':
                return \is_object($other);
            case 'resource':
                return \is_resource($other) || \is_string(@\get_resource_type($other));
            case 'scalar':
                return \is_scalar($other);
            case 'callable':
                return \is_callable($other);
            case 'iterable':
                return \is_iterable($other);
        }
    }
}