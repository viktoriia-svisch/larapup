<?php
namespace SebastianBergmann\CodeCoverage\Report\Xml;
class File
{
    private $dom;
    private $contextNode;
    public function __construct(\DOMElement $context)
    {
        $this->dom         = $context->ownerDocument;
        $this->contextNode = $context;
    }
    public function getTotals(): Totals
    {
        $totalsContainer = $this->contextNode->firstChild;
        if (!$totalsContainer) {
            $totalsContainer = $this->contextNode->appendChild(
                $this->dom->createElementNS(
                    'https:
                    'totals'
                )
            );
        }
        return new Totals($totalsContainer);
    }
    public function getLineCoverage(string $line): Coverage
    {
        $coverage = $this->contextNode->getElementsByTagNameNS(
            'https:
            'coverage'
        )->item(0);
        if (!$coverage) {
            $coverage = $this->contextNode->appendChild(
                $this->dom->createElementNS(
                    'https:
                    'coverage'
                )
            );
        }
        $lineNode = $coverage->appendChild(
            $this->dom->createElementNS(
                'https:
                'line'
            )
        );
        return new Coverage($lineNode, $line);
    }
    protected function getContextNode(): \DOMElement
    {
        return $this->contextNode;
    }
    protected function getDomDocument(): \DOMDocument
    {
        return $this->dom;
    }
}
