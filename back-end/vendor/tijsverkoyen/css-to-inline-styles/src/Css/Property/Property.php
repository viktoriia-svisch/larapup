<?php
namespace TijsVerkoyen\CssToInlineStyles\Css\Property;
use Symfony\Component\CssSelector\Node\Specificity;
final class Property
{
    private $name;
    private $value;
    private $originalSpecificity;
    public function __construct($name, $value, Specificity $specificity = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->originalSpecificity = $specificity;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function getOriginalSpecificity()
    {
        return $this->originalSpecificity;
    }
    public function isImportant()
    {
        return (stripos($this->value, '!important') !== false);
    }
    public function toString()
    {
        return sprintf(
            '%1$s: %2$s;',
            $this->name,
            $this->value
        );
    }
}
