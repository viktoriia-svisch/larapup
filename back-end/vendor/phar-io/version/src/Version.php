<?php
namespace PharIo\Version;
class Version {
    private $major;
    private $minor;
    private $patch;
    private $preReleaseSuffix;
    private $versionString = '';
    public function __construct($versionString) {
        $this->ensureVersionStringIsValid($versionString);
        $this->versionString = $versionString;
    }
    public function getPreReleaseSuffix() {
        return $this->preReleaseSuffix;
    }
    public function getVersionString() {
        return $this->versionString;
    }
    public function hasPreReleaseSuffix() {
        return $this->preReleaseSuffix !== null;
    }
    public function isGreaterThan(Version $version) {
        if ($version->getMajor()->getValue() > $this->getMajor()->getValue()) {
            return false;
        }
        if ($version->getMajor()->getValue() < $this->getMajor()->getValue()) {
            return true;
        }
        if ($version->getMinor()->getValue() > $this->getMinor()->getValue()) {
            return false;
        }
        if ($version->getMinor()->getValue() < $this->getMinor()->getValue()) {
            return true;
        }
        if ($version->getPatch()->getValue() > $this->getPatch()->getValue()) {
            return false;
        }
        if ($version->getPatch()->getValue() < $this->getPatch()->getValue()) {
            return true;
        }
        if (!$version->hasPreReleaseSuffix() && !$this->hasPreReleaseSuffix()) {
            return false;
        }
        if ($version->hasPreReleaseSuffix() && !$this->hasPreReleaseSuffix()) {
            return true;
        }
        if (!$version->hasPreReleaseSuffix() && $this->hasPreReleaseSuffix()) {
            return false;
        }
        return $this->getPreReleaseSuffix()->isGreaterThan($version->getPreReleaseSuffix());
    }
    public function getMajor() {
        return $this->major;
    }
    public function getMinor() {
        return $this->minor;
    }
    public function getPatch() {
        return $this->patch;
    }
    private function parseVersion(array $matches) {
        $this->major = new VersionNumber($matches['Major']);
        $this->minor = new VersionNumber($matches['Minor']);
        $this->patch = isset($matches['Patch']) ? new VersionNumber($matches['Patch']) : new VersionNumber(null);
        if (isset($matches['PreReleaseSuffix'])) {
            $this->preReleaseSuffix = new PreReleaseSuffix($matches['PreReleaseSuffix']);
        }
    }
    private function ensureVersionStringIsValid($version) {
        $regex = '/^v?
            (?<Major>(0|(?:[1-9][0-9]*)))
            \\.
            (?<Minor>(0|(?:[1-9][0-9]*)))
            (\\.
                (?<Patch>(0|(?:[1-9][0-9]*)))
            )?
            (?:
                -
                (?<PreReleaseSuffix>(?:(dev|beta|b|RC|alpha|a|patch|p)\.?\d*))
            )?       
        $/x';
        if (preg_match($regex, $version, $matches) !== 1) {
            throw new InvalidVersionException(
                sprintf("Version string '%s' does not follow SemVer semantics", $version)
            );
        }
        $this->parseVersion($matches);
    }
}
