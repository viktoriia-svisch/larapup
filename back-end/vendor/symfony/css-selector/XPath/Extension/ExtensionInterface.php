<?php
namespace Symfony\Component\CssSelector\XPath\Extension;
interface ExtensionInterface
{
    public function getNodeTranslators();
    public function getCombinationTranslators();
    public function getFunctionTranslators();
    public function getPseudoClassTranslators();
    public function getAttributeMatchingTranslators();
    public function getName();
}
