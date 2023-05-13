<?php
namespace SebastianBergmann\CodeCoverage\Report\Xml;
final class Report extends File
{
    public function __construct(string $name)
    {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><phpunit xmlns="https:
        $contextNode = $dom->getElementsByTagNameNS(
            'https:
            'file'
        )->item(0);
        parent::__construct($contextNode);
        $this->setName($name);
    }
    public function asDom(): \DOMDocument
    {
        return $this->getDomDocument();
    }
    public function getFunctionObject($name): Method
    {
        $node = $this->getContextNode()->appendChild(
            $this->getDomDocument()->createElementNS(
                'https:
                'function'
            )
        );
        return new Method($node, $name);
    }
    public function getClassObject($name): Unit
    {
        return $this->getUnitObject('class', $name);
    }
    public function getTraitObject($name): Unit
    {
        return $this->getUnitObject('trait', $name);
    }
    public function getSource(): Source
    {
        $source = $this->getContextNode()->getElementsByTagNameNS(
            'https:
            'source'
        )->item(0);
        if (!$source) {
            $source = $this->getContextNode()->appendChild(
                $this->getDomDocument()->createElementNS(
                    'https:
                    'source'
                )
            );
        }
        return new Source($source);
    }
    private function setName($name): void
    {
        $this->getContextNode()->setAttribute('name', \basename($name));
        $this->getContextNode()->setAttribute('path', \dirname($name));
    }
    private function getUnitObject($tagName, $name): Unit
    {
        $node = $this->getContextNode()->appendChild(
            $this->getDomDocument()->createElementNS(
                'https:
                $tagName
            )
        );
        return new Unit($node, $name);
    }
}
