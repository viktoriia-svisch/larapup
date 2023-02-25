<?php
namespace PharIo\Manifest;
class ComponentElement extends ManifestElement {
    public function getName() {
        return $this->getAttributeValue('name');
    }
    public function getVersion() {
        return $this->getAttributeValue('version');
    }
}
