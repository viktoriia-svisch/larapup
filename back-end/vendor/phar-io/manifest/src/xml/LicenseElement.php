<?php
namespace PharIo\Manifest;
class LicenseElement extends ManifestElement {
    public function getType() {
        return $this->getAttributeValue('type');
    }
    public function getUrl() {
        return $this->getAttributeValue('url');
    }
}
