<?php
namespace PharIo\Manifest;
class Author {
    private $name;
    private $email;
    public function __construct($name, Email $email) {
        $this->name  = $name;
        $this->email = $email;
    }
    public function getName() {
        return $this->name;
    }
    public function getEmail() {
        return $this->email;
    }
    public function __toString() {
        return sprintf(
            '%s <%s>',
            $this->name,
            $this->email
        );
    }
}
