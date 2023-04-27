<?php
namespace PharIo\Manifest;
class CopyrightElement extends ManifestElement {
    public function getAuthorElements() {
        return new AuthorElementCollection(
            $this->getChildrenByName('author')
        );
    }
    public function getLicenseElement() {
        return new LicenseElement(
            $this->getChildByName('license')
        );
    }
}
