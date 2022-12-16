<?php
namespace Symfony\Component\CssSelector\Parser\Shortcut;
use Symfony\Component\CssSelector\Node\ElementNode;
use Symfony\Component\CssSelector\Node\HashNode;
use Symfony\Component\CssSelector\Node\SelectorNode;
use Symfony\Component\CssSelector\Parser\ParserInterface;
class HashParser implements ParserInterface
{
    public function parse(string $source): array
    {
        if (preg_match('/^(?:([a-z]++)\|)?+([\w-]++|\*)?+#([\w-]++)$/i', trim($source), $matches)) {
            return [
                new SelectorNode(new HashNode(new ElementNode($matches[1] ?: null, $matches[2] ?: null), $matches[3])),
            ];
        }
        return [];
    }
}
