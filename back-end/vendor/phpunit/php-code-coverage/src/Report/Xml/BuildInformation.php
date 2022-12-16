<?php
namespace SebastianBergmann\CodeCoverage\Report\Xml;
use SebastianBergmann\Environment\Runtime;
final class BuildInformation
{
    private $contextNode;
    public function __construct(\DOMElement $contextNode)
    {
        $this->contextNode = $contextNode;
    }
    public function setRuntimeInformation(Runtime $runtime): void
    {
        $runtimeNode = $this->getNodeByName('runtime');
        $runtimeNode->setAttribute('name', $runtime->getName());
        $runtimeNode->setAttribute('version', $runtime->getVersion());
        $runtimeNode->setAttribute('url', $runtime->getVendorUrl());
        $driverNode = $this->getNodeByName('driver');
        if ($runtime->hasPHPDBGCodeCoverage()) {
            $driverNode->setAttribute('name', 'phpdbg');
            $driverNode->setAttribute('version', \constant('PHPDBG_VERSION'));
        }
        if ($runtime->hasXdebug()) {
            $driverNode->setAttribute('name', 'xdebug');
            $driverNode->setAttribute('version', \phpversion('xdebug'));
        }
    }
    public function setBuildTime(\DateTime $date): void
    {
        $this->contextNode->setAttribute('time', $date->format('D M j G:i:s T Y'));
    }
    public function setGeneratorVersions(string $phpUnitVersion, string $coverageVersion): void
    {
        $this->contextNode->setAttribute('phpunit', $phpUnitVersion);
        $this->contextNode->setAttribute('coverage', $coverageVersion);
    }
    private function getNodeByName(string $name): \DOMElement
    {
        $node = $this->contextNode->getElementsByTagNameNS(
            'https:
            $name
        )->item(0);
        if (!$node) {
            $node = $this->contextNode->appendChild(
                $this->contextNode->ownerDocument->createElementNS(
                    'https:
                    $name
                )
            );
        }
        return $node;
    }
}
