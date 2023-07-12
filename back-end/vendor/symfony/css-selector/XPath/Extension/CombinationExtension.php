<?php
namespace Symfony\Component\CssSelector\XPath\Extension;
use Symfony\Component\CssSelector\XPath\XPathExpr;
class CombinationExtension extends AbstractExtension
{
    public function getCombinationTranslators(): array
    {
        return [
            ' ' => [$this, 'translateDescendant'],
            '>' => [$this, 'translateChild'],
            '+' => [$this, 'translateDirectAdjacent'],
            '~' => [$this, 'translateIndirectAdjacent'],
        ];
    }
    public function translateDescendant(XPathExpr $xpath, XPathExpr $combinedXpath): XPathExpr
    {
        return $xpath->join('/descendant-or-self::*/', $combinedXpath);
    }
    public function translateChild(XPathExpr $xpath, XPathExpr $combinedXpath)
    {
        return $xpath->join('/', $combinedXpath);
    }
    public function translateDirectAdjacent(XPathExpr $xpath, XPathExpr $combinedXpath)
    {
        return $xpath
            ->join('/following-sibling::', $combinedXpath)
            ->addNameTest()
            ->addCondition('position() = 1');
    }
    public function translateIndirectAdjacent(XPathExpr $xpath, XPathExpr $combinedXpath)
    {
        return $xpath->join('/following-sibling::', $combinedXpath);
    }
    public function getName()
    {
        return 'combination';
    }
}
