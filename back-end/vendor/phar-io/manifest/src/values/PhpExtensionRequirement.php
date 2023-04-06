<?php
namespace PharIo\Manifest;
class PhpExtensionRequirement implements Requirement {
    private $extension;
    public function __construct($extension) {
        $this->extension = $extension;
    }
    public function __toString() {
        return $this->extension;
    }
}
