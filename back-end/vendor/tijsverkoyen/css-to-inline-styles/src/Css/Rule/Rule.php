<?php
namespace TijsVerkoyen\CssToInlineStyles\Css\Rule;
use Symfony\Component\CssSelector\Node\Specificity;
final class Rule
{
    private $selector;
    private $properties;
    private $specificity;
    private $order;
    public function __construct($selector, array $properties, Specificity $specificity, $order)
    {
        $this->selector = $selector;
        $this->properties = $properties;
        $this->specificity = $specificity;
        $this->order = $order;
    }
    public function getSelector()
    {
        return $this->selector;
    }
    public function getProperties()
    {
        return $this->properties;
    }
    public function getSpecificity()
    {
        return $this->specificity;
    }
    public function getOrder()
    {
        return $this->order;
    }
}
