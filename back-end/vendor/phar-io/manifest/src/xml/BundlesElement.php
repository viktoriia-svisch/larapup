<?php
namespace PharIo\Manifest;
class BundlesElement extends ManifestElement {
    public function getComponentElements() {
        return new ComponentElementCollection(
            $this->getChildrenByName('component')
        );
    }
}
