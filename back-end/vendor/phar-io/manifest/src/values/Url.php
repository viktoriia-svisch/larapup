<?php
namespace PharIo\Manifest;
class Url {
    private $url;
    public function __construct($url) {
        $this->ensureUrlIsValid($url);
        $this->url = $url;
    }
    public function __toString() {
        return $this->url;
    }
    private function ensureUrlIsValid($url) {
        if (filter_var($url, \FILTER_VALIDATE_URL) === false) {
            throw new InvalidUrlException;
        }
    }
}
