<?php
namespace PharIo\Manifest;
class AuthorElement extends ManifestElement {
    public function getName() {
        return $this->getAttributeValue('name');
    }
    public function getEmail() {
        return $this->getAttributeValue('email');
    }
}
