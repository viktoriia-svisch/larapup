<?php
namespace PharIo\Manifest;
use PharIo\Version\VersionConstraint;
class PhpVersionRequirement implements Requirement {
    private $versionConstraint;
    public function __construct(VersionConstraint $versionConstraint) {
        $this->versionConstraint = $versionConstraint;
    }
    public function getVersionConstraint() {
        return $this->versionConstraint;
    }
}
