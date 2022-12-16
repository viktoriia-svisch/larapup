<?php
namespace PharIo\Version;
class AnyVersionConstraint implements VersionConstraint {
    public function complies(Version $version) {
        return true;
    }
    public function asString() {
        return '*';
    }
}
