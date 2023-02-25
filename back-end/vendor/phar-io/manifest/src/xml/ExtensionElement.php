<?php
namespace PharIo\Manifest;
class ExtensionElement extends ManifestElement {
    public function getFor() {
        return $this->getAttributeValue('for');
    }
    public function getCompatible() {
        return $this->getAttributeValue('compatible');
    }
}
