<?php
namespace PharIo\Version;
class VersionConstraintValue {
    private $major;
    private $minor;
    private $patch;
    private $label = '';
    private $buildMetaData = '';
    private $versionString = '';
    public function __construct($versionString) {
        $this->versionString = $versionString;
        $this->parseVersion($versionString);
    }
    public function getLabel() {
        return $this->label;
    }
    public function getBuildMetaData() {
        return $this->buildMetaData;
    }
    public function getVersionString() {
        return $this->versionString;
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
    private function parseVersion($versionString) {
        $this->extractBuildMetaData($versionString);
        $this->extractLabel($versionString);
        $versionSegments = explode('.', $versionString);
        $this->major = new VersionNumber($versionSegments[0]);
        $minorValue = isset($versionSegments[1]) ? $versionSegments[1] : null;
        $patchValue = isset($versionSegments[2]) ? $versionSegments[2] : null;
        $this->minor = new VersionNumber($minorValue);
        $this->patch = new VersionNumber($patchValue);
    }
    private function extractBuildMetaData(&$versionString) {
        if (preg_match('/\+(.*)/', $versionString, $matches) == 1) {
            $this->buildMetaData = $matches[1];
            $versionString = str_replace($matches[0], '', $versionString);
        }
    }
    private function extractLabel(&$versionString) {
        if (preg_match('/\-(.*)/', $versionString, $matches) == 1) {
            $this->label = $matches[1];
            $versionString = str_replace($matches[0], '', $versionString);
        }
    }
}
