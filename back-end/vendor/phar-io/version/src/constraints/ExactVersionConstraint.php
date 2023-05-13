<?php
namespace PharIo\Version;
class ExactVersionConstraint extends AbstractVersionConstraint {
    public function complies(Version $version) {
        return $this->asString() == $version->getVersionString();
    }
}
