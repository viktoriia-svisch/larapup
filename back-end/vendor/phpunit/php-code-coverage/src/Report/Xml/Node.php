<?php
namespace SebastianBergmann\CodeCoverage\Report\Xml;
abstract class Node
{
    private $dom;
    private $contextNode;
    public function __construct(\DOMElement $context)
    {
        $this->setContextNode($context);
    }
    public function getDom(): \DOMDocument
    {
        return $this->dom;
    }
    public function getTotals(): Totals
    {
        $totalsContainer = $this->getContextNode()->firstChild;
        if (!$totalsContainer) {
            $totalsContainer = $this->getContextNode()->appendChild(
                $this->dom->createElementNS(
                    'https:
                    'totals'
                )
            );
        }
        return new Totals($totalsContainer);
    }
    public function addDirectory(string $name): Directory
    {
        $dirNode = $this->getDom()->createElementNS(
            'https:
            'directory'
        );
        $dirNode->setAttribute('name', $name);
        $this->getContextNode()->appendChild($dirNode);
        return new Directory($dirNode);
    }
    public function addFile(string $name, string $href): File
    {
        $fileNode = $this->getDom()->createElementNS(
            'https:
            'file'
        );
        $fileNode->setAttribute('name', $name);
        $fileNode->setAttribute('href', $href);
        $this->getContextNode()->appendChild($fileNode);
        return new File($fileNode);
    }
    protected function setContextNode(\DOMElement $context): void
    {
        $this->dom         = $context->ownerDocument;
        $this->contextNode = $context;
    }
    protected function getContextNode(): \DOMElement
    {
        return $this->contextNode;
    }
}
