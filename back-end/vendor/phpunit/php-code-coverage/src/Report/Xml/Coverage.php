<?php
namespace SebastianBergmann\CodeCoverage\Report\Xml;
use SebastianBergmann\CodeCoverage\RuntimeException;
final class Coverage
{
    private $writer;
    private $contextNode;
    private $finalized = false;
    public function __construct(\DOMElement $context, string $line)
    {
        $this->contextNode = $context;
        $this->writer = new \XMLWriter();
        $this->writer->openMemory();
        $this->writer->startElementNS(null, $context->nodeName, 'https:
        $this->writer->writeAttribute('nr', $line);
    }
    public function addTest(string $test): void
    {
        if ($this->finalized) {
            throw new RuntimeException('Coverage Report already finalized');
        }
        $this->writer->startElement('covered');
        $this->writer->writeAttribute('by', $test);
        $this->writer->endElement();
    }
    public function finalize(): void
    {
        $this->writer->endElement();
        $fragment = $this->contextNode->ownerDocument->createDocumentFragment();
        $fragment->appendXML($this->writer->outputMemory());
        $this->contextNode->parentNode->replaceChild(
            $fragment,
            $this->contextNode
        );
        $this->finalized = true;
    }
}
