<?php
namespace PharIo\Manifest;
class Email {
    private $email;
    public function __construct($email) {
        $this->ensureEmailIsValid($email);
        $this->email = $email;
    }
    public function __toString() {
        return $this->email;
    }
    private function ensureEmailIsValid($url) {
        if (filter_var($url, \FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidEmailException;
        }
    }
}
