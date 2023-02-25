<?php
namespace SebastianBergmann\CodeCoverage\Report\Xml;
use SebastianBergmann\CodeCoverage\Util;
final class Totals
{
    private $container;
    private $linesNode;
    private $methodsNode;
    private $functionsNode;
    private $classesNode;
    private $traitsNode;
    public function __construct(\DOMElement $container)
    {
        $this->container = $container;
        $dom             = $container->ownerDocument;
        $this->linesNode = $dom->createElementNS(
            'https:
            'lines'
        );
        $this->methodsNode = $dom->createElementNS(
            'https:
            'methods'
        );
        $this->functionsNode = $dom->createElementNS(
            'https:
            'functions'
        );
        $this->classesNode = $dom->createElementNS(
            'https:
            'classes'
        );
        $this->traitsNode = $dom->createElementNS(
            'https:
            'traits'
        );
        $container->appendChild($this->linesNode);
        $container->appendChild($this->methodsNode);
        $container->appendChild($this->functionsNode);
        $container->appendChild($this->classesNode);
        $container->appendChild($this->traitsNode);
    }
    public function getContainer(): \DOMNode
    {
        return $this->container;
    }
    public function setNumLines(int $loc, int $cloc, int $ncloc, int $executable, int $executed): void
    {
        $this->linesNode->setAttribute('total', $loc);
        $this->linesNode->setAttribute('comments', $cloc);
        $this->linesNode->setAttribute('code', $ncloc);
        $this->linesNode->setAttribute('executable', $executable);
        $this->linesNode->setAttribute('executed', $executed);
        $this->linesNode->setAttribute(
            'percent',
            $executable === 0 ? 0 : \sprintf('%01.2F', Util::percent($executed, $executable))
        );
    }
    public function setNumClasses(int $count, int $tested): void
    {
        $this->classesNode->setAttribute('count', $count);
        $this->classesNode->setAttribute('tested', $tested);
        $this->classesNode->setAttribute(
            'percent',
            $count === 0 ? 0 : \sprintf('%01.2F', Util::percent($tested, $count))
        );
    }
    public function setNumTraits(int $count, int $tested): void
    {
        $this->traitsNode->setAttribute('count', $count);
        $this->traitsNode->setAttribute('tested', $tested);
        $this->traitsNode->setAttribute(
            'percent',
            $count === 0 ? 0 : \sprintf('%01.2F', Util::percent($tested, $count))
        );
    }
    public function setNumMethods(int $count, int $tested): void
    {
        $this->methodsNode->setAttribute('count', $count);
        $this->methodsNode->setAttribute('tested', $tested);
        $this->methodsNode->setAttribute(
            'percent',
            $count === 0 ? 0 : \sprintf('%01.2F', Util::percent($tested, $count))
        );
    }
    public function setNumFunctions(int $count, int $tested): void
    {
        $this->functionsNode->setAttribute('count', $count);
        $this->functionsNode->setAttribute('tested', $tested);
        $this->functionsNode->setAttribute(
            'percent',
            $count === 0 ? 0 : \sprintf('%01.2F', Util::percent($tested, $count))
        );
    }
}
