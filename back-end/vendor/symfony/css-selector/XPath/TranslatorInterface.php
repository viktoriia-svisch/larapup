<?php
namespace Symfony\Component\CssSelector\XPath;
use Symfony\Component\CssSelector\Node\SelectorNode;
interface TranslatorInterface
{
    public function cssToXPath(string $cssExpr, string $prefix = 'descendant-or-self::'): string;
    public function selectorToXPath(SelectorNode $selector, string $prefix = 'descendant-or-self::'): string;
}
