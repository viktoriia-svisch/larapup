<?php
namespace PharIo\Manifest;
class ApplicationName {
    private $name;
    public function __construct($name) {
        $this->ensureIsString($name);
        $this->ensureValidFormat($name);
        $this->name = $name;
    }
    public function __toString() {
        return $this->name;
    }
    public function isEqual(ApplicationName $name) {
        return $this->name === $name->name;
    }
    private function ensureValidFormat($name) {
        if (!preg_match('#\w/\w#', $name)) {
            throw new InvalidApplicationNameException(
                sprintf('Format of name "%s" is not valid - expected: vendor/packagename', $name),
                InvalidApplicationNameException::InvalidFormat
            );
        }
    }
    private function ensureIsString($name) {
        if (!is_string($name)) {
            throw new InvalidApplicationNameException(
                'Name must be a string',
                InvalidApplicationNameException::NotAString
            );
        }
    }
}
