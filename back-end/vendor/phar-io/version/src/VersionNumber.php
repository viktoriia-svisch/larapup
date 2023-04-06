<?php
namespace PharIo\Version;
class VersionNumber {
    private $value;
    public function __construct($value) {
        if (is_numeric($value)) {
            $this->value = $value;
        }
    }
    public function isAny() {
        return $this->value === null;
    }
    public function getValue() {
        return $this->value;
    }
}
