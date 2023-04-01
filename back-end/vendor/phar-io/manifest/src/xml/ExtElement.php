<?php
namespace PharIo\Manifest;
class ExtElement extends ManifestElement {
    public function getName() {
        return $this->getAttributeValue('name');
    }
}
