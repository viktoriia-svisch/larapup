<?php
namespace Symfony\Component\HttpKernel\ControllerMetadata;
class ArgumentMetadata
{
    private $name;
    private $type;
    private $isVariadic;
    private $hasDefaultValue;
    private $defaultValue;
    private $isNullable;
    public function __construct(string $name, ?string $type, bool $isVariadic, bool $hasDefaultValue, $defaultValue, bool $isNullable = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->isVariadic = $isVariadic;
        $this->hasDefaultValue = $hasDefaultValue;
        $this->defaultValue = $defaultValue;
        $this->isNullable = $isNullable || null === $type || ($hasDefaultValue && null === $defaultValue);
    }
    public function getName()
    {
        return $this->name;
    }
    public function getType()
    {
        return $this->type;
    }
    public function isVariadic()
    {
        return $this->isVariadic;
    }
    public function hasDefaultValue()
    {
        return $this->hasDefaultValue;
    }
    public function isNullable()
    {
        return $this->isNullable;
    }
    public function getDefaultValue()
    {
        if (!$this->hasDefaultValue) {
            throw new \LogicException(sprintf('Argument $%s does not have a default value. Use %s::hasDefaultValue() to avoid this exception.', $this->name, __CLASS__));
        }
        return $this->defaultValue;
    }
}
