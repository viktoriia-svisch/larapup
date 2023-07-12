<?php
namespace SebastianBergmann\CodeCoverage\Report\Xml;
use TheSeer\Tokenizer\NamespaceUri;
use TheSeer\Tokenizer\Tokenizer;
use TheSeer\Tokenizer\XMLSerializer;
final class Source
{
    private $context;
    public function __construct(\DOMElement $context)
    {
        $this->context = $context;
    }
    public function setSourceCode(string $source): void
    {
        $context = $this->context;
        $tokens = (new Tokenizer())->parse($source);
        $srcDom = (new XMLSerializer(new NamespaceUri($context->namespaceURI)))->toDom($tokens);
        $context->parentNode->replaceChild(
            $context->ownerDocument->importNode($srcDom->documentElement, true),
            $context
        );
    }
}
