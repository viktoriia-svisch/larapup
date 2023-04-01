<?php
namespace Symfony\Component\CssSelector\Parser\Shortcut;
use Symfony\Component\CssSelector\Node\ElementNode;
use Symfony\Component\CssSelector\Node\SelectorNode;
use Symfony\Component\CssSelector\Parser\ParserInterface;
class EmptyStringParser implements ParserInterface
{
    public function parse(string $source): array
    {
        if ('' == $source) {
            return [new SelectorNode(new ElementNode(null, '*'))];
        }
        return [];
    }
}
