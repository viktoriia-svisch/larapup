<?php
namespace PharIo\Manifest;
use PharIo\Version\Version;
class Manifest {
    private $name;
    private $version;
    private $type;
    private $copyrightInformation;
    private $requirements;
    private $bundledComponents;
    public function __construct(ApplicationName $name, Version $version, Type $type, CopyrightInformation $copyrightInformation, RequirementCollection $requirements, BundledComponentCollection $bundledComponents) {
        $this->name                 = $name;
        $this->version              = $version;
        $this->type                 = $type;
        $this->copyrightInformation = $copyrightInformation;
        $this->requirements         = $requirements;
        $this->bundledComponents    = $bundledComponents;
    }
    public function getName() {
        return $this->name;
    }
    public function getVersion() {
        return $this->version;
    }
    public function getType() {
        return $this->type;
    }
    public function getCopyrightInformation() {
        return $this->copyrightInformation;
    }
    public function getRequirements() {
        return $this->requirements;
    }
    public function getBundledComponents() {
        return $this->bundledComponents;
    }
    public function isApplication() {
        return $this->type->isApplication();
    }
    public function isLibrary() {
        return $this->type->isLibrary();
    }
    public function isExtension() {
        return $this->type->isExtension();
    }
    public function isExtensionFor(ApplicationName $application, Version $version = null) {
        if (!$this->isExtension()) {
            return false;
        }
        $type = $this->type;
        if ($version !== null) {
            return $type->isCompatibleWith($application, $version);
        }
        return $type->isExtensionFor($application);
    }
}
