<?php
namespace PharIo\Version;
abstract class AbstractVersionConstraint implements VersionConstraint {
    private $originalValue = '';
    public function __construct($originalValue) {
        $this->originalValue = $originalValue;
    }
    public function asString() {
        return $this->originalValue;
    }
}
