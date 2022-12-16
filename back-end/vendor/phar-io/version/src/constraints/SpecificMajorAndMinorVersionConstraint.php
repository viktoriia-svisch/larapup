<?php
namespace PharIo\Version;
class SpecificMajorAndMinorVersionConstraint extends AbstractVersionConstraint {
    private $major = 0;
    private $minor = 0;
    public function __construct($originalValue, $major, $minor) {
        parent::__construct($originalValue);
        $this->major = $major;
        $this->minor = $minor;
    }
    public function complies(Version $version) {
        if ($version->getMajor()->getValue() != $this->major) {
            return false;
        }
        return $version->getMinor()->getValue() == $this->minor;
    }
}
