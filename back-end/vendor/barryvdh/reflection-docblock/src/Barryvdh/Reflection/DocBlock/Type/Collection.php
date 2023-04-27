<?php
namespace Barryvdh\Reflection\DocBlock\Type;
use Barryvdh\Reflection\DocBlock\Context;
class Collection extends \ArrayObject
{
    const OPERATOR_OR = '|';
    const OPERATOR_ARRAY = '[]';
    const OPERATOR_NAMESPACE = '\\';
    protected static $keywords = array(
        'string', 'int', 'integer', 'bool', 'boolean', 'float', 'double',
        'object', 'mixed', 'array', 'resource', 'void', 'null', 'scalar',
        'callback', 'callable', 'false', 'true', 'self', '$this', 'static'
    );
    protected $context = null;
    public function __construct(
        array $types = array(),
        Context $context = null
    ) {
        $this->context = null === $context ? new Context() : $context;
        foreach ($types as $type) {
            $this->add($type);
        }
    }
    public function getContext()
    {
        return $this->context;
    }
    public function add($type)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException(
                'A type should be represented by a string, received: '
                .var_export($type, true)
            );
        }
        $type_parts = explode(self::OPERATOR_OR, $type);
        foreach ($type_parts as $part) {
            $expanded_type = $this->expand($part);
            if ($expanded_type) {
                $this[] = $expanded_type;
            }
        }
    }
    public function __toString()
    {
        return implode(self::OPERATOR_OR, $this->getArrayCopy());
    }
    protected function expand($type)
    {
        $type = trim($type);
        if (!$type) {
            return '';
        }
        if ($this->isTypeAnArray($type)) {
            return $this->expand(substr($type, 0, -2)) . self::OPERATOR_ARRAY;
        }
        if ($this->isRelativeType($type) && !$this->isTypeAKeyword($type)) {
            if($this->shouldBeAbsolute($type)){
                return self::OPERATOR_NAMESPACE . $type;
            }
            $type_parts = explode(self::OPERATOR_NAMESPACE, $type, 2);
            $namespace_aliases = $this->context->getNamespaceAliases();
            if (!isset($namespace_aliases[$type_parts[0]]) &&
                !isset($namespace_aliases[strstr($type_parts[0], '::', true)])) {
                $namespace = $this->context->getNamespace();
                if ('' !== $namespace) {
                    $namespace .= self::OPERATOR_NAMESPACE;
                }
                return self::OPERATOR_NAMESPACE . $namespace . $type;
            }
            if (strpos($type_parts[0], '::')) {
                $type_parts[] = strstr($type_parts[0], '::');
                $type_parts[0] = $namespace_aliases[strstr($type_parts[0], '::', true)];
                return implode('', $type_parts);
            }
            $type_parts[0] = $namespace_aliases[$type_parts[0]];
            $type = implode(self::OPERATOR_NAMESPACE, $type_parts);
        }
        return $type;
    }
    protected function isTypeAnArray($type)
    {
        return substr($type, -2) === self::OPERATOR_ARRAY;
    }
    protected function isTypeAKeyword($type)
    {
        return in_array(strtolower($type), static::$keywords, true);
    }
    protected function isRelativeType($type)
    {
        return ($type[0] !== self::OPERATOR_NAMESPACE)
            || $this->isTypeAKeyword($type);
    }
    protected function shouldBeAbsolute($type){
        return class_exists($type);
    }
}
