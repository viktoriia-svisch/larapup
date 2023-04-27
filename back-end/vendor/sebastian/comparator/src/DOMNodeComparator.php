<?php
namespace SebastianBergmann\Comparator;
use DOMDocument;
use DOMNode;
class DOMNodeComparator extends ObjectComparator
{
    public function accepts($expected, $actual)
    {
        return $expected instanceof DOMNode && $actual instanceof DOMNode;
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false, array &$processed = [])
    {
        $expectedAsString = $this->nodeToText($expected, true, $ignoreCase);
        $actualAsString   = $this->nodeToText($actual, true, $ignoreCase);
        if ($expectedAsString !== $actualAsString) {
            $type = $expected instanceof DOMDocument ? 'documents' : 'nodes';
            throw new ComparisonFailure(
                $expected,
                $actual,
                $expectedAsString,
                $actualAsString,
                false,
                \sprintf("Failed asserting that two DOM %s are equal.\n", $type)
            );
        }
    }
    private function nodeToText(DOMNode $node, bool $canonicalize, bool $ignoreCase): string
    {
        if ($canonicalize) {
            $document = new DOMDocument;
            @$document->loadXML($node->C14N());
            $node = $document;
        }
        $document = $node instanceof DOMDocument ? $node : $node->ownerDocument;
        $document->formatOutput = true;
        $document->normalizeDocument();
        $text = $node instanceof DOMDocument ? $node->saveXML() : $document->saveXML($node);
        return $ignoreCase ? \strtolower($text) : $text;
    }
}
