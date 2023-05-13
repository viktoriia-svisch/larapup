<?php
namespace SebastianBergmann\CodeCoverage\Report\Xml;
final class Project extends Node
{
    public function __construct(string $directory)
    {
        $this->init();
        $this->setProjectSourceDirectory($directory);
    }
    public function getProjectSourceDirectory(): string
    {
        return $this->getContextNode()->getAttribute('source');
    }
    public function getBuildInformation(): BuildInformation
    {
        $buildNode = $this->getDom()->getElementsByTagNameNS(
            'https:
            'build'
        )->item(0);
        if (!$buildNode) {
            $buildNode = $this->getDom()->documentElement->appendChild(
                $this->getDom()->createElementNS(
                    'https:
                    'build'
                )
            );
        }
        return new BuildInformation($buildNode);
    }
    public function getTests(): Tests
    {
        $testsNode = $this->getContextNode()->getElementsByTagNameNS(
            'https:
            'tests'
        )->item(0);
        if (!$testsNode) {
            $testsNode = $this->getContextNode()->appendChild(
                $this->getDom()->createElementNS(
                    'https:
                    'tests'
                )
            );
        }
        return new Tests($testsNode);
    }
    public function asDom(): \DOMDocument
    {
        return $this->getDom();
    }
    private function init(): void
    {
        $dom = new \DOMDocument;
        $dom->loadXML('<?xml version="1.0" ?><phpunit xmlns="https:
        $this->setContextNode(
            $dom->getElementsByTagNameNS(
                'https:
                'project'
            )->item(0)
        );
    }
    private function setProjectSourceDirectory(string $name): void
    {
        $this->getContextNode()->setAttribute('source', $name);
    }
}
