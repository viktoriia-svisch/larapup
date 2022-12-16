<?php
namespace PharIo\Version;
class SpecificMajorVersionConstraint extends AbstractVersionConstraint {
    private $major = 0;
    public function __construct($originalValue, $major) {
        parent::__construct($originalValue);
        $this->major = $major;
    }
    public function complies(Version $version) {
        return $version->getMajor()->getValue() == $this->major;
    }
}
