<?php
namespace phpDocumentor\Reflection;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
final class TypeResolver
{
    const OPERATOR_ARRAY = '[]';
    const OPERATOR_NAMESPACE = '\\';
    private $keywords = array(
        'string' => Types\String_::class,
        'int' => Types\Integer::class,
        'integer' => Types\Integer::class,
        'bool' => Types\Boolean::class,
        'boolean' => Types\Boolean::class,
        'float' => Types\Float_::class,
        'double' => Types\Float_::class,
        'object' => Object_::class,
        'mixed' => Types\Mixed_::class,
        'array' => Array_::class,
        'resource' => Types\Resource_::class,
        'void' => Types\Void_::class,
        'null' => Types\Null_::class,
        'scalar' => Types\Scalar::class,
        'callback' => Types\Callable_::class,
        'callable' => Types\Callable_::class,
        'false' => Types\Boolean::class,
        'true' => Types\Boolean::class,
        'self' => Types\Self_::class,
        '$this' => Types\This::class,
        'static' => Types\Static_::class,
        'parent' => Types\Parent_::class,
        'iterable' => Iterable_::class,
    );
    private $fqsenResolver;
    public function __construct(FqsenResolver $fqsenResolver = null)
    {
        $this->fqsenResolver = $fqsenResolver ?: new FqsenResolver();
    }
    public function resolve($type, Context $context = null)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException(
                'Attempted to resolve type but it appeared not to be a string, received: ' . var_export($type, true)
            );
        }
        $type = trim($type);
        if (!$type) {
            throw new \InvalidArgumentException('Attempted to resolve "' . $type . '" but it appears to be empty');
        }
        if ($context === null) {
            $context = new Context('');
        }
        switch (true) {
            case $this->isNullableType($type):
                return $this->resolveNullableType($type, $context);
            case $this->isKeyword($type):
                return $this->resolveKeyword($type);
            case ($this->isCompoundType($type)):
                return $this->resolveCompoundType($type, $context);
            case $this->isTypedArray($type):
                return $this->resolveTypedArray($type, $context);
            case $this->isFqsen($type):
                return $this->resolveTypedObject($type);
            case $this->isPartialStructuralElementName($type):
                return $this->resolveTypedObject($type, $context);
            default:
                throw new \RuntimeException(
                    'Unable to resolve type "' . $type . '", there is no known method to resolve it'
                );
        }
    }
    public function addKeyword($keyword, $typeClassName)
    {
        if (!class_exists($typeClassName)) {
            throw new \InvalidArgumentException(
                'The Value Object that needs to be created with a keyword "' . $keyword . '" must be an existing class'
                . ' but we could not find the class ' . $typeClassName
            );
        }
        if (!in_array(Type::class, class_implements($typeClassName))) {
            throw new \InvalidArgumentException(
                'The class "' . $typeClassName . '" must implement the interface "phpDocumentor\Reflection\Type"'
            );
        }
        $this->keywords[$keyword] = $typeClassName;
    }
    private function isTypedArray($type)
    {
        return substr($type, -2) === self::OPERATOR_ARRAY;
    }
    private function isKeyword($type)
    {
        return in_array(strtolower($type), array_keys($this->keywords), true);
    }
    private function isPartialStructuralElementName($type)
    {
        return ($type[0] !== self::OPERATOR_NAMESPACE) && !$this->isKeyword($type);
    }
    private function isFqsen($type)
    {
        return strpos($type, self::OPERATOR_NAMESPACE) === 0;
    }
    private function isCompoundType($type)
    {
        return strpos($type, '|') !== false;
    }
    private function isNullableType($type)
    {
        return $type[0] === '?';
    }
    private function resolveTypedArray($type, Context $context)
    {
        return new Array_($this->resolve(substr($type, 0, -2), $context));
    }
    private function resolveKeyword($type)
    {
        $className = $this->keywords[strtolower($type)];
        return new $className();
    }
    private function resolveTypedObject($type, Context $context = null)
    {
        return new Object_($this->fqsenResolver->resolve($type, $context));
    }
    private function resolveCompoundType($type, Context $context)
    {
        $types = [];
        foreach (explode('|', $type) as $part) {
            $types[] = $this->resolve($part, $context);
        }
        return new Compound($types);
    }
    private function resolveNullableType($type, Context $context)
    {
        return new Nullable($this->resolve(ltrim($type, '?'), $context));
    }
}
