<?php
namespace PharIo\Manifest;
class RequiresElement extends ManifestElement {
    public function getPHPElement() {
        return new PhpElement(
            $this->getChildByName('php')
        );
    }
}
