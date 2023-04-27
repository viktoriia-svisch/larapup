<?php
namespace SebastianBergmann\CodeCoverage\Report\Xml;
final class Unit
{
    private $contextNode;
    public function __construct(\DOMElement $context, string $name)
    {
        $this->contextNode = $context;
        $this->setName($name);
    }
    public function setLines(int $start, int $executable, int $executed): void
    {
        $this->contextNode->setAttribute('start', $start);
        $this->contextNode->setAttribute('executable', $executable);
        $this->contextNode->setAttribute('executed', $executed);
    }
    public function setCrap(float $crap): void
    {
        $this->contextNode->setAttribute('crap', $crap);
    }
    public function setPackage(string $full, string $package, string $sub, string $category): void
    {
        $node = $this->contextNode->getElementsByTagNameNS(
            'https:
            'package'
        )->item(0);
        if (!$node) {
            $node = $this->contextNode->appendChild(
                $this->contextNode->ownerDocument->createElementNS(
                    'https:
                    'package'
                )
            );
        }
        $node->setAttribute('full', $full);
        $node->setAttribute('name', $package);
        $node->setAttribute('sub', $sub);
        $node->setAttribute('category', $category);
    }
    public function setNamespace(string $namespace): void
    {
        $node = $this->contextNode->getElementsByTagNameNS(
            'https:
            'namespace'
        )->item(0);
        if (!$node) {
            $node = $this->contextNode->appendChild(
                $this->contextNode->ownerDocument->createElementNS(
                    'https:
                    'namespace'
                )
            );
        }
        $node->setAttribute('name', $namespace);
    }
    public function addMethod(string $name): Method
    {
        $node = $this->contextNode->appendChild(
            $this->contextNode->ownerDocument->createElementNS(
                'https:
                'method'
            )
        );
        return new Method($node, $name);
    }
    private function setName(string $name): void
    {
        $this->contextNode->setAttribute('name', $name);
    }
}
