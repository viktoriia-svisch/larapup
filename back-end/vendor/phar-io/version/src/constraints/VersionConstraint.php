<?php
namespace PharIo\Version;
interface VersionConstraint {
    public function complies(Version $version);
    public function asString();
}
