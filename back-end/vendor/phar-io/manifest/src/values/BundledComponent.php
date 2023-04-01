<?php
namespace PharIo\Manifest;
use PharIo\Version\Version;
class BundledComponent {
    private $name;
    private $version;
    public function __construct($name, Version $version) {
        $this->name    = $name;
        $this->version = $version;
    }
    public function getName() {
        return $this->name;
    }
    public function getVersion() {
        return $this->version;
    }
}
