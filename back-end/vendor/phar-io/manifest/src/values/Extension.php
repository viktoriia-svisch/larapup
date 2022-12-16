<?php
namespace PharIo\Manifest;
use PharIo\Version\Version;
use PharIo\Version\VersionConstraint;
class Extension extends Type {
    private $application;
    private $versionConstraint;
    public function __construct(ApplicationName $application, VersionConstraint $versionConstraint) {
        $this->application       = $application;
        $this->versionConstraint = $versionConstraint;
    }
    public function getApplicationName() {
        return $this->application;
    }
    public function getVersionConstraint() {
        return $this->versionConstraint;
    }
    public function isExtension() {
        return true;
    }
    public function isExtensionFor(ApplicationName $name) {
        return $this->application->isEqual($name);
    }
    public function isCompatibleWith(ApplicationName $name, Version $version) {
        return $this->isExtensionFor($name) && $this->versionConstraint->complies($version);
    }
}
