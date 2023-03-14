<?php
namespace PharIo\Version;
class GreaterThanOrEqualToVersionConstraint extends AbstractVersionConstraint {
    private $minimalVersion;
    public function __construct($originalValue, Version $minimalVersion) {
        parent::__construct($originalValue);
        $this->minimalVersion = $minimalVersion;
    }
    public function complies(Version $version) {
        return $version->getVersionString() == $this->minimalVersion->getVersionString()
            || $version->isGreaterThan($this->minimalVersion);
    }
}
