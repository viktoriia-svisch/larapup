<?php
namespace Symfony\Component\CssSelector\XPath\Extension;
abstract class AbstractExtension implements ExtensionInterface
{
    public function getNodeTranslators()
    {
        return [];
    }
    public function getCombinationTranslators()
    {
        return [];
    }
    public function getFunctionTranslators()
    {
        return [];
    }
    public function getPseudoClassTranslators()
    {
        return [];
    }
    public function getAttributeMatchingTranslators()
    {
        return [];
    }
}
